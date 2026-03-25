<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Safra extends AbstractBoleto implements BoletoContract
{
    /**
     * Local de pagamento
     *
     * @var string
     */
    protected $localPagamento = 'Pagável em qualquer Banco';

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_SAFRA;

    /**
     * Variáveis adicionais.
     *
     * @var array
     */
    public $variaveis_adicionais = [
        'carteira_nome' => '',
    ];

    /**
     * Define as carteiras disponíveis para este banco
     *
     * @var array
     */
    protected $carteiras = ['1', '2'];

    /**
     * Espécie do documento, coódigo para remessa
     *
     * @var string
     */
    protected $especiesCodigo = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'DS' => '09',
        'OUTROS' => '99'
    ];

    protected $especiesCodigo400 = [
        'DM' => '01',
        'NP' => '02',
        'NS' => '03',
        'REC' => '05',
        'DS' => '09',
        'OUTROS' => '99'
    ];

    /**
     * Seta dias para baixa automática
     *
     * @param int $baixaAutomatica
     *
     * @return $this
     * @throws \Exception
     */
    public function setDiasBaixaAutomatica($baixaAutomatica)
    {
        if ($this->getDiasProtesto() > 0) {
            throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
        }
        $baixaAutomatica = (int) $baixaAutomatica;
        $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
        return $this;
    }

    /**
     * Gera o Nosso Número.
     *
     * @return string
     * @throws \Exception
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 9);
    }

    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getNossoNumero();
    }

    /**
     *
     * @return type
     */
    public function getAgencia()
    {
        return Util::numberFormatGeral(parent::getAgencia(), 5);
    }

    /**
     *
     * @return type
     */
    public function getConta()
    {
        return Util::numberFormatGeral(parent::getConta(), 9);
    }

    /**
     * Método para gerar o código da posição de 25 a 34
     *
     * @return string
     * @throws \Exception
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }
        return $this->campoLivre = "7"
            . Util::numberFormatGeral($this->getAgencia(), 5)
            . Util::numberFormatGeral($this->getConta(), 9)
            . Util::numberFormatGeral($this->getNossoNumero(), 9)
            . "2";
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    public static function parseCampoLivre($campoLivre)
    {
        return [
            'convenio' => null,
            'agenciaDv' => null,
            'codigoCliente' => null,
            'agencia' => substr($campoLivre, 0, 5),
            'contaCorrente' => substr($campoLivre, 5, 9),
            'nossoNumero' => substr($campoLivre, 9, 9)
        ];
    }

    public function getAceite(): string
    {
        if (parent::getAceite() == 'S' || parent::getAceite() == 'SIM') {
            return 'SIM';
        } else {
            return 'NÃO';
        }
    }
}
