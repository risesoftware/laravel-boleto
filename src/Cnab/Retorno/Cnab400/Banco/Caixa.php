<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Arr;

class Caixa extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_CEF;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '01' => 'Entrada Confirmada',
        '02' => 'Baixa Confirmada',
        '03' => 'Abatimento Concedido',
        '04' => 'Abatimento Cancelado',
        '05' => 'Vencimento Alterado',
        '06' => 'Uso da Empresa Alterado',
        '07' => 'Prazo de Protesto Alterado',
        '08' => 'Prazo de Devolução Alterado',
        '09' => 'Alteração Confirmada',
        '10' => 'Alteração com Reemissão de Bloqueto Confirmada',
        '11' => 'Alteração da Opção de Protesto para Devolução',
        '12' => 'Alteração da Opção de Devolução para protesto',
        '20' => 'Em Ser',
        '21' => 'Liquidação',
        '22' => 'Liquidação em Cartório',
        '23' => 'Baixa por Devolução',
        '24' => 'Baixa por Franco Pagamento',
        '25' => 'Baixa por Protesto',
        '26' => 'Título enviado para Cartório',
        '27' => 'Sustação de Protesto',
        '28' => 'Estorno de Protesto',
        '29' => 'Estorno de Sustação de Protesto',
        '30' => 'Alteração de Título',
        '31' => 'Tarifa sobre Título Vencido',
        '32' => 'Outras Tarifas de Alteração',
        '33' => 'Estorno de Baixa/Liquidação',
        '34' => 'Transferência de Carteira/Entrada',
        '35' => 'Transferência de Carteira/Baixa',
        '99' => 'Rejeição do Título – Cód. Rejeição informado nas POS 80 a 82'
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Movimento sem Cedente Correspondente ',
        '02' => 'Movimento sem Título Correspondente',
        '08' => 'Movimento para Título já com Movimentação no dia ',
        '09' => 'Nosso Número não Pertence ao Cedente',
        '10' => 'Inclusão de Título já Existente',
        '12' => 'Movimento Duplicado',
        '13' => 'Entrada Inválida para Cobrança Caucionada (Cedente não possui conta Caução)',
        '20' => 'CEP do Sacado não Encontrado (Não foi possível a Determinação da Agência Cobradora para o Título) ',
        '21' => 'Agência Cobradora não Encontrada (Agência Designada para Cobradora não Cadastrada no Sistema) ',
        '22' => 'Agência Cedente não Encontrada (Agência do Cedente não Cadastrada no Sistema)',
        '45' => 'Data de Vencimento com prazo mais de 1 ano',
        '49' => 'Movimento Inválido para Título Baixado/Liquidado',
        '50' => 'Movimento Inválido para Título enviado ao Cartório',
        '54' => 'Faixa de CEP da Agência Cobradora não Abrange CEP do Sacado',
        '55' => 'Título já com Opção de Devolução',
        '56' => 'Processo de Protesto em Andamento',
        '57' => 'Título já com Opção de Protesto',
        '58' => 'Processo de Devolução em Andamento',
        '59' => 'Novo Prazo p/ Protesto/Devolução Inválido',
        '76' => 'Alteração de Prazo de Protesto Inválida',
        '77' => 'Alteração de Prazo de Devolução Inválida',
        '81' => 'CEP do Sacado Inválido',
        '82' => 'CGC/CPF do Sacado Inválido (Dígito não Confere)',
        '83' => 'Número do Documento (Seu Número) inválido',
        '84' => 'Protesto inválido para título sem Número do Documento (Seu Número)',
    ];

    /**
     * Roda antes dos metodos de processar
     */
    protected function init()
    {
        $this->totais = [
            'qtdTitulos' => 0,
            'vlrTitulos' => 0,
            'qtdLiquidados' => 0,
            'vlrLiquidados' => 0,
            'qtdEntradas' => 0,
            'vlrEntradas' => 0,
            'qtdBaixados' => 0,
            'vlrBaixados' => 0,
            'qtdProtestados' => 0,
            'vlrProtestados' => 0,
            'qtdAlterados' => 0,
            'vlrAlterados' => 0,
            'qtdErros' => 0,
        ];
    }

    /**
     * @param array $header
     *
     * @return bool
     * @throws \Exception
     */
    protected function processarHeader(array $header)
    {
        $this->getHeader()
            ->setOperacaoCodigo($this->rem(2, 2, $header))
            ->setOperacao($this->rem(3, 9, $header))
            ->setServicoCodigo($this->rem(10, 11, $header))
            ->setServico($this->rem(12, 26, $header))
            ->setAgencia($this->rem(27, 30, $header))
            ->setCodigoCliente($this->rem(31, 36, $header))
            ->setData($this->rem(95, 100, $header));

        if ($this->rem(159, 161, $header) == '007') {
            $this->getHeader()->setCodigoCliente($this->rem(31, 37, $header));
        }

        return true;
    }

    /**
     * @param array $detalhe
     *
     * @return bool
     * @throws \Exception
     */
    protected function processarDetalhe(array $detalhe)
    {
        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(107, 108, $detalhe))
            ->setNossoNumero($this->rem(57, 73, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle((int) $this->rem(32, 56, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(294, 299, $detalhe))
            ->setLinhaRegistro($this->rem(395, 400, $detalhe));

        if ($this->usandoCentavos) {
            $d->setValor((int) $this->rem(153, 165, $detalhe))
                ->setValorTarifa((int) $this->rem(176, 188, $detalhe))
                ->setValorIOF((int) $this->rem(215, 227, $detalhe))
                ->setValorAbatimento((int) $this->rem(228, 240, $detalhe))
                ->setValorDesconto((int) $this->rem(241, 253, $detalhe))
                ->setValorRecebido((int) $this->rem(254, 266, $detalhe))
                ->setValorMora((int) $this->rem(267, 279, $detalhe))
                ->setValorMulta((int) $this->rem(280, 292, $detalhe));
        } else {
            $d->setValor((float) Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
                ->setValorTarifa((float) Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
                ->setValorIOF((float) Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
                ->setValorAbatimento((float) Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
                ->setValorDesconto((float) Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
                ->setValorRecebido((float) Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
                ->setValorMora((float) Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
                ->setValorMulta((float) Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));
        }

        if ($d->hasOcorrencia('21', '22', '35')) {
            $this->totais['qtdLiquidados']++;
            $this->totais['vlrLiquidados'] += $d->getValorRecebido();
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('01')) {
            $this->totais['qtdEntradas']++;
            $this->totais['vlrEntradas'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('02', '23', '25')) {
            $this->totais['qtdBaixados']++;
            $this->totais['vlrBaixados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('26', '56')) {
            $this->totais['qtdProtestados']++;
            $this->totais['vlrProtestados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('05', '06', '07', '08', '09', '10', '11', '12', '30')) {
            $this->totais['qtdAlterados']++;
            $this->totais['vlrAlterados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('99')) {
            $this->totais['qtdErros']++;
            $d->setError(Arr::get($this->rejeicoes, $this->rem(80, 82, $detalhe), 'Consulte seu Internet Banking'));
        } else {
            $d->setOcorrenciaTipo($d::OCORRENCIA_OUTROS);
        }

        $this->totais['qtdTitulos']++;
        $this->totais['vlrTitulos'] += $d->getValor();

        return true;
    }

    /**
     * @param array $trailer
     *
     * @return bool
     */
    protected function processarTrailer(array $trailer)
    {
        $totais = $this->getTrailer()
            ->setQuantidadeTitulos((int) $this->totais['qtdTitulos'])
            ->setQuantidadeLiquidados((int) $this->totais['qtdLiquidados'])
            ->setQuantidadeEntradas((int) $this->totais['qtdEntradas'])
            ->setQuantidadeBaixados((int) $this->totais['qtdBaixados'])
            ->setQuantidadeAlterados((int) $this->totais['qtdAlterados'])
            ->setQuantidadeConfirmacaoInstrucaoProtestos((int) $this->totais['qtdProtestados'])
            ->setQuantidadeErros((int) $this->totais['qtdErros']);

        if ($this->usandoCentavos) {
            $totais->setValorTitulos((int) $this->totais['vlrTitulos'])
                ->setValorLiquidados((int) $this->totais['vlrLiquidados'])
                ->setValorEntradas((int) $this->totais['vlrEntradas'])
                ->setValorBaixados((int) $this->totais['vlrBaixados'])
                ->setValorAlterados((int) $this->totais['vlrAlterados'])
                ->setValorConfirmacaoInstrucaoProtestos((int) $this->totais['vlrProtestados']);
        } else {
            $totais->setValorTitulos((float) Util::nFloat($this->totais['vlrTitulos'] / 100, 2, false))
                ->setValorLiquidados((float) Util::nFloat($this->totais['vlrLiquidados'] / 100, 2, false))
                ->setValorEntradas((float) Util::nFloat($this->totais['vlrEntradas'] / 100, 2, false))
                ->setValorBaixados((float) Util::nFloat($this->totais['vlrBaixados'] / 100, 2, false))
                ->setValorAlterados((float) Util::nFloat($this->totais['vlrAlterados'] / 100, 2, false))
                ->setValorConfirmacaoInstrucaoProtestos((float) Util::nFloat($this->totais['vlrProtestados'] / 100, 2, false));
        }

        return true;
    }
}
