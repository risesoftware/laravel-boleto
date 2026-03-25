<?php

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Util;

class Safra extends AbstractRemessa implements RemessaContract
{
    const ESPECIE_DUPLICATA = '01';
    const ESPECIE_NOTA_PROMISSORIA = '02';
    const ESPECIE_NOTA_SEGURO = '03';
    const ESPECIE_MENSALIDADE_ESCOLAR = '04';
    const ESPECIE_RECIBO = '05';
    const ESPECIE_CONTRATO = '06';
    const ESPECIE_COSSEGUROS = '07';
    const ESPECIE_DUPLICATA_SERVIÇO = '08';
    const ESPECIE_LETRA_CAMBIO = '09';
    const ESPECIE_NOTA_DEBITOS = '13';
    const ESPECIE_DOCUMENTO_DIVIDA = '15';
    const ESPECIE_ENCARGOS_CONDOMINIAIS = '16';
    const ESPECIE_NOTA_SERVICOS = '17';
    const ESPECIE_DIVERSOS = '99';

    const OCORRENCIA_ENTRADA_TITULO = '01';

    const INSTRUCAO_SEM = '00';
    const INSTRUCAO_NAO_RECEBER_PRINCIPAL = '01';
    const INSTRUCAO_DEVOL_VENC_15 = '02';
    const INSTRUCAO_DEVOL_VENC_30 = '03';
    const INSTRUCAO_NAO_PROTESTAR = '07';
    const INSTRUCAO_COBRAR_MORA = '01';
    const INSTRUCAO_NAO_COBRAR_MORA = '08';
    const INSTRUCAO_COBRAR_MULTA = '16';
    const INSTRUCAO_PROTESTO_AUTOMATICO = '10';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_SAFRA;

    /**
     * Define as carteiras disponíveis para cada banco
     *
     * @var array
     */
    protected $carteiras = ['1', '2'];

    /**
     * Caracter de fim de linha
     *
     * @var string
     */
    protected $fimLinha = "\r\n";

    /**
     * Caracter de fim de arquivo
     *
     * @var null
     */
    protected $fimArquivo = "\r\n";

    /**
     * Valor total dos títulos
     * @var flaot
     */
    private $valorTotalTitulos;

    protected function header()
    {
        $this->iniciaHeader();

        $this->add(1, 1, '0');
        $this->add(2, 2, '1');
        $this->add(3, 9, 'REMESSA');
        $this->add(10, 11, '01');
        $this->add(12, 26, Util::formatCnab('X', 'COBRANCA', 15));
        $this->add(27, 31, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(32, 40, Util::formatCnab('9', $this->getConta(), 9));
        $this->add(41, 46, '');
        $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
        $this->add(77, 79, $this->getCodigoBanco());
        $this->add(80, 90, Util::formatCnab('X', 'BANCO SAFRA', 11));
        $this->add(91, 94, '');
        $this->add(95, 100, date('dmy'));
        $this->add(101, 391, '');
        $this->add(392, 394, '001');
        $this->add(395, 400, '000001');

        return $this;
    }

    public function addBoleto(BoletoContract $boleto)
    {
        $this->iniciaDetalhe();
        $this->add(1, 1, '1');
        $this->add(2, 3, strlen(Util::onlyNumbers($this->getBeneficiario()->getDocumento())) == 14 ? '02' : '01');
        $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14));
        $this->add(18, 22, Util::formatCnab('9', $this->getAgencia(), 5));
        $this->add(23, 31, Util::formatCnab('9', $this->getConta(), 9));
        $this->add(32, 37, '');
        $this->add(38, 62, Util::formatCnab('X', $boleto->getNumeroControle(), 25)); // numero de controle
        $this->add(63, 71, Util::formatCnab('9', $boleto->getNossoNumero(), 9));
        $this->add(72, 85, '');
        $this->add(85, 91, ''); //data de inicio de cobranca de mora
        $this->add(92, 92, ''); // uso branco
        $this->add(93, 101, ''); // branco
        $this->add(102, 102, '0'); //codigo de iof (0 : isento, 1: 2%, 2: 4%)
        $this->add(103, 104, '00'); // moeda
        $this->add(105, 105, '');

        if ($boleto->getDiasProtesto() > 0) {
            $this->add(106, 107, Util::formatCnab('9', $boleto->getDiasProtesto(), 2)); // DIAS PARA PROTESTO
        } else {
            $this->add(106, 107, '00'); // SEM OCORRENCIA DE PROTESTO
        }

        $this->add(108, 108, Util::formatCnab('9', $this->getCarteiraNumero(), 1));
        $this->add(109, 110, self::OCORRENCIA_ENTRADA_TITULO); // REGISTRO (SOMENTE ISSO???)
        $this->add(111, 120, Util::formatCnab('9', $boleto->getNumeroDocumento(), 10));
        $this->add(121, 126, $boleto->getDataVencimento()->format('dmy'));
        $this->add(127, 139, Util::formatCnab('9', $this->addTotal($boleto->getValor()), 13));
        $this->add(140, 142, $this->getCodigoBanco());
        $this->add(143, 147, '00000'); // agencia encarregada da cobranca ver se é a do cliente
        $this->add(148, 149, $boleto->getEspecieDocCodigo());
        $this->add(150, 150, $boleto->getAceite() == 'SIM' ? 'A' : 'N');
        $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));

        // Verifica dias de baixo automática
        if ($boleto->getDiasBaixaAutomatica() > 0 && $boleto->getDiasBaixaAutomatica() <= 15) {
            $this->add(157, 158, self::INSTRUCAO_DEVOL_VENC_15);
        } else if ($boleto->getDiasBaixaAutomatica() > 15) {
            $this->add(157, 158, self::INSTRUCAO_DEVOL_VENC_30);

            // Verifica instrução multa
        } else if ($boleto->getMulta() > 0) {
            $this->add(157, 158, self::INSTRUCAO_COBRAR_MULTA);
            // Somente juros NOTA 1 - Item C
        } else if ($boleto->getMulta() == 0 && $boleto->getJuros() == 0) {
            $this->add(157, 158, self::INSTRUCAO_NAO_COBRAR_MORA);
        } else {
            $this->add(157, 158, self::INSTRUCAO_NAO_RECEBER_PRINCIPAL);
        }

        // Verifica protesto
        if ($boleto->getDiasProtesto() > 0) {
            $this->add(159, 160, self::INSTRUCAO_PROTESTO_AUTOMATICO);
        } else {
            $this->add(159, 160, self::INSTRUCAO_NAO_PROTESTAR);
        }

        // Verifica juros
        if ($boleto->getJuros() > 0) {
            $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13));
        } else {
            $this->add(161, 173, Util::formatCnab('9', 0, 13));
        }

        $this->add(174, 179, $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000');
        $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13));
        $this->add(193, 205, Util::formatCnab('9', 0, 13, 2)); // valor de iof da ioeracao

        // Verifica multa
        if ($boleto->getMulta() > 0) {
            $this->add(206, 211, $boleto->getDataVencimento()->format('dmy')); // Data da multa
            $this->add(212, 215, Util::formatCnab('9', Util::nFloat($boleto->getMulta()), 4));
            $this->add(216, 218, '000');
        } else {
            $this->add(206, 218, Util::formatCnab('9', 0, 13));
        }

        $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
        $this->add(221, 234, Util::formatCnab('9L', $boleto->getPagador()->getDocumento(), 14));
        $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
        // $this->add(265, 274, '');
        $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
        $this->add(315, 324, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 10));
        $this->add(325, 326, '');
        $this->add(327, 334, Util::formatCnab('9L', $boleto->getPagador()->getCep(), 8));
        $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
        $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
        $this->add(352, 381, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 30));
        $this->add(382, 384, ''); // dias para baixa
        $this->add(385, 387, '');
        $this->add(388, 388, ''); // indicador de tipo de desconto 1
        $this->add(389, 391, $this->getCodigoBanco());
        $this->add(392, 394, '001'); // numero sequencial da remessa
        $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

        return $this;
    }

    /**
     * Retorna o valor de um boleto somando ao valor total dos títulos
     * @param float
     * @return float
     */
    protected function addTotal($valor)
    {
        $this->valorTotalTitulos += $valor;
        return $valor;
    }

    protected function trailer()
    {
        $this->iniciaTrailer();

        $this->add(1, 1, '9');
        $this->add(2, 368, '');
        $this->add(369, 376, Util::formatCnab('9', $this->getCount() - 2, 8));
        $this->add(377, 391, Util::formatCnab('9', $this->addTotal($this->valorTotalTitulos), 15));
        $this->add(392, 394, '001');
        $this->add(395, 400, Util::formatCnab('9', $this->getCount(), 6));

        return $this;
    }
}
