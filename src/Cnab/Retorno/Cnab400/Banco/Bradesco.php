<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Arr;

class Bradesco extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BRADESCO;

    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        "02" => "Entrada Confirmada",
        "03" => "Entrada Rejeitada",
        "06" => "Liquidação normal",
        "09" => "Baixado Automat. via Arquivo",
        "10" => "Baixado conforme instruções da Agência",
        "11" => "Em Ser - Arquivo de Títulos pendentes",
        "12" => "Abatimento Concedido",
        "13" => "Abatimento Cancelado",
        "14" => "Vencimento Alterado",
        "15" => "Liquidação em Cartório",
        "16" => "Título Pago em Cheque - Vinculado",
        "17" => "Liquidação após baixa ou Título não registrado",
        "18" => "Acerto de Depositária",
        "19" => "Confirmação Receb. Inst. de Protesto",
        "20" => "Confirmação Recebimento Instrução Sustação de Protesto",
        "21" => "Acerto do Controle do Participante",
        "22" => "Título Com Pagamento Cancelado",
        "23" => "Entrada do Título em Cartório",
        "24" => "Entrada rejeitada por CEP Irregular",
        "25" => "Confirmação Receb.Inst.de Protesto Falimentar",
        "27" => "Baixa Rejeitada",
        "28" => "Débito de tarifas/custas",
        "29" => "Ocorrências do Pagador",
        "30" => "Alteração de Outros Dados Rejeitados",
        "32" => "Instrução Rejeitada",
        "33" => "Confirmação Pedido Alteração Outros Dados",
        "34" => "Retirado de Cartório e Manutenção Carteira",
        "35" => "Desagendamento do débito automático",
        "40" => "Estorno de pagamento (Novo)",
        "55" => "Sustado judicial (Novo)",
        "68" => "Acerto dos dados do rateio de Crédito",
        "69" => "Cancelamento dos dados do rateio",
        "73" => "Confirmação Receb. Pedido de Negativação",
        "74" => "Confir Pedido de Excl de Negat (com ou sem baixa)"
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '03' => [
            '02' => 'Código do registro detalhe inválido',
            '03' => 'Código da ocorrência inválida',
            '04' => 'Código de ocorrência não permitida para a carteira',
            '05' => 'Código de ocorrência não numérico',
            '07' => 'Agência/conta/Digito - inválido',
            '08' => 'Nosso número inválido',
            '09' => 'Nosso número duplicado',
            '10' => 'Carteira inválida',
            '13' => 'Identificação da emissão do bloqueto inválida',
            '16' => 'Data de vencimento inválida',
            '18' => 'Vencimento fora do prazo de operação',
            '20' => 'Valor do Título inválido',
            '21' => 'Espécie do Título inválida',
            '22' => 'Espécie não permitida para a carteira',
            '23' => 'Tipo Pagamento não contratado',
            '24' => 'Data de emissão inválida',
            '27' => 'Valor/Taxa de Juros Mora Invalido',
            '28' => 'Código do desconto inválido',
            '29' => 'Valor Desconto > ou = valor título',
            '32' => 'Valor do IOF Invalido',
            '34' => 'Valor do Abatimento Maior ou Igual ao Valor do Título',
            '38' => 'Prazo para protesto/ Negativação inválido (ALTERADO)',
            '39' => 'Pedido de Protesto/Negativação não Permitida para o Título',
            '44' => 'Agência Beneficiário não prevista',
            '45' => 'Nome do pagador não informado',
            '46' => 'Tipo/número de inscrição do pagador inválidos',
            '47' => 'Endereço do pagador não informado',
            '48' => 'CEP Inválido',
            '49' => 'CEP sem Praça de Cobrança',
            '50' => 'CEP irregular - Banco Correspondente',
            '53' => 'Tipo/Número de inscrição do Sacador Avalista Inválido',
            '59' => 'Valor/Percentual da Multa Inválido',
            '63' => 'Entrada para Título já cadastrado',
            '65' => 'Limite excedido',
            '66' => 'Número autorização inexistente',
            '68' => 'Débito não agendado - erro nos dados de remessa',
            '69' => 'Débito não agendado - Pagador não consta no cadastro de autorizante',
            '70' => 'Débito não agendado - Beneficiário não autorizado pelo Pagador',
            '71' => 'Débito não agendado - Beneficiário não participa do débito Automático',
            '72' => 'Débito não agendado - Código de moeda diferente de R$',
            '73' => 'Débito não agendado - Data de vencimento inválida',
            '74' => 'Débito não agendado - Conforme seu pedido, Título não registrado',
            '75' => 'Débito não agendado – Tipo de número de inscrição do debitado inválido',
            '79' => 'Data de Juros de Mora Invalida',
            '80' => 'Data do Desconto Invalida',
            '86' => 'Seu Número Invalido'
        ],
        '24' => [
            '48' => 'CEP inválido',
            '49' => 'CEP sem praça de Cobrança'
        ],
        '27' => [
            '02' => 'Código do registro detalhe inválido',
            '04' => 'Código de ocorrência não permitido para a carteira',
            '07' => 'Agência/Conta/dígito inválidos',
            '08' => 'Nosso número inválido',
            '09' => 'Nosso Número Duplicado',
            '10' => 'Carteira inválida',
            '15' => 'Carteira/Agência/Conta/nosso número inválidos',
            '16' => 'Data Vencimento Invalida',
            '18' => 'Vencimento Fora do Prazo de Operação',
            '20' => 'Valor Título Invalido',
            '40' => 'Título com ordem de protesto emitido',
            '42' => 'Código para baixa/devolução inválido',
            '45' => 'Nome do sacado não informado ou invalido',
            '46' => 'Tipo/Número de Inscrição do Sacado Invalido',
            '47' => 'Endereço do sacado não informado',
            '48' => 'CEP Invalido',
            '60' => 'Movimento para Título não cadastrado',
            '77' => 'Transferência para desconto não permitido para a carteira',
            '85' => 'Título com pagamento vinculado',
            '86' => 'Seu Número Invalido'
        ],
        '30' => [
            '01' => 'Código do Banco inválido',
            '04' => 'Código de ocorrência não permitido para a carteira',
            '05' => 'Código da ocorrência não numérico',
            '08' => 'Nosso número inválido',
            '15' => 'Característica da cobrança incompatível',
            '16' => 'Data de vencimento inválido',
            '17' => 'Data de vencimento anterior a data de emissão',
            '18' => 'Vencimento fora do prazo de operação',
            '20' => 'Valor título invalido',
            '21' => 'Especie título invalida',
            '22' => 'Especie não permitida para a carteira',
            '23' => 'Tipo pagamento não contratado',
            '24' => 'Data de emissão Inválida',
            '26' => 'Código de juros de mora inválido (*)',
            '27' => 'Valor/taxa de juros de mora inválido',
            '28' => 'Código de desconto inválido',
            '29' => 'Valor do desconto maior/igual ao valor do Título',
            '30' => 'Desconto a conceder não confere',
            '31' => 'Concessão de desconto já existente ( Desconto anterior )',
            '32' => 'Valor do IOF inválido',
            '33' => 'Valor do abatimento inválido',
            '34' => 'Valor do abatimento maior/igual ao valor do Título',
            '36' => 'Concessão Abatimento',
            '38' => 'Prazo para protesto/ Negativação inválido',
            '39' => 'Pedido para protesto/ Negativação não permitido para o título',
            '40' => 'Título com ordem/pedido de protesto/Negativação emitido',
            '42' => 'Código para baixa/devolução inválido',
            '43' => 'Prazo para Baixa/Devolução Invalido',
            '46' => 'Tipo/número de inscrição do pagador inválidos',
            '48' => 'Cep Inválido',
            '53' => 'Tipo/Número de inscrição do pagador/avalista inválidos',
            '54' => 'Pagador/avalista não informado',
            '57' => 'Código da multa inválido',
            '58' => 'Data da multa inválida',
            '60' => 'Movimento para Título não cadastrado',
            '79' => 'Data de Juros de mora Inválida',
            '80' => 'Data do desconto inválida',
            '85' => 'Título com Pagamento Vinculado',
            '88' => 'E-mail Pagador não lido no prazo 5 dias',
            '91' => 'E-mail pagador não recebido'
        ],
        '32' => [
            '01' => 'Código do Banco inválido',
            '02' => 'Código Registro Detalhe Invalido',
            '04' => 'Código de ocorrência não permitido para a carteira',
            '05' => 'Código de ocorrência não numérico',
            '06' => 'Espécie BDP, não aceita Pagamento Parcial',
            '07' => 'Agência/Conta/dígito inválidos',
            '08' => 'Nosso número inválido',
            '10' => 'Carteira inválida',
            '15' => 'Características da cobrança incompatíveis',
            '16' => 'Data de vencimento inválida',
            '17' => 'Data de vencimento anterior a data de emissão',
            '18' => 'Vencimento fora do prazo de operação',
            '20' => 'Valor do título inválido',
            '21' => 'Espécie do Título inválida',
            '22' => 'Espécie não permitida para a carteira',
            '23' => 'Tipo Pagamento não contratado',
            '24' => 'Data de emissão inválida',
            '26' => 'Código Juros Mora Invalido',
            '27' => 'Valor/Taxa Juros Mira Invalido',
            '28' => 'Código de desconto inválido',
            '29' => 'Valor do desconto maior/igual ao valor do Título',
            '30' => 'Desconto a conceder não confere',
            '31' => 'Concessão de desconto - Já existe desconto anterior',
            '33' => 'Valor do abatimento inválido',
            '34' => 'Valor do abatimento maior/igual ao valor do Título',
            '36' => 'Concessão abatimento - Já existe abatimento anterior',
            '38' => 'Prazo para protesto/ Negativação inválido',
            '39' => 'Pedido para protesto/ Negativação não permitido para o título',
            '40' => 'Título com ordem/pedido de protesto/Negativação emitido',
            '41' => 'Pedido de sustação/excl p/ Título sem instrução de protesto/Negativação',
            '45' => 'Nome do Pagador não informado',
            '46' => 'Tipo/número de inscrição do Pagador inválidos',
            '47' => 'Endereço do Pagador não informado',
            '48' => 'CEP Inválido',
            '50' => 'CEP referente a um Banco correspondente',
            '52' => 'Unidade da Federação Invalida',
            '53' => 'Tipo de inscrição do pagador avalista inválidos',
            '60' => 'Movimento para Título não cadastrado',
            '65' => 'Limite Excedido',
            '66' => 'Numero Autorização Inexistente',
            '85' => 'Título com pagamento vinculado',
            '86' => 'Seu número inválido',
            '94' => 'Título Cessão Fiduciária – Instrução Não Liberada pela Agência',
            '97' => 'Instrução não permitida título negativado',
            '98' => 'Inclusão Bloqueada face a determinação Judicial',
            '99' => 'Telefone beneficiário não informado / inconsistente'
        ]
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
            'qtdErros' => 0
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
            ->setCodigoCliente($this->rem(27, 46, $header))
            ->setData($this->rem(95, 100, $header));

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
        if ($this->count() == 1) {
            $this->getHeader()
                ->setAgencia($this->rem(25, 29, $detalhe))
                ->setConta($this->rem(30, 36, $detalhe))
                ->setContaDv($this->rem(37, 37, $detalhe));
        }

        $d = $this->detalheAtual();
        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(71, 82, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle((int) $this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setBancoOrigemCheque($this->rem(315, 318, $detalhe))
            ->setLinhaRegistro($this->rem(395, 400, $detalhe));

        if ($this->usandoCentavos) {
            $d->setValor((int) $this->rem(153, 165, $detalhe))
                ->setValorTarifa((int) $this->rem(176, 188, $detalhe))
                ->setValorOutrasDespesas((int) $this->rem(189, 201, $detalhe))
                ->setValorIOF((int) $this->rem(215, 227, $detalhe))
                ->setValorAbatimento((int) $this->rem(228, 240, $detalhe))
                ->setValorDesconto((int) $this->rem(241, 253, $detalhe))
                ->setValorRecebido((int) $this->rem(254, 266, $detalhe))
                ->setValorMora((int) $this->rem(267, 279, $detalhe)) // bradesco usa juros e multa no mesmo campo
                ->setValorOutrasReceitas((int) $this->rem(280, 292, $detalhe));
        } else {
            $d->setValor((float) Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
                ->setValorTarifa((float) Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
                ->setValorOutrasDespesas((float) Util::nFloat($this->rem(189, 201, $detalhe) / 100, 2, false))
                ->setValorIOF((float) Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
                ->setValorAbatimento((float) Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
                ->setValorDesconto((float) Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
                ->setValorRecebido((float) Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
                ->setValorMora((float) Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false)) // bradesco usa juros e multa no mesmo campo
                ->setValorOutrasReceitas((float) Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));
        }

        $msgAdicional = str_split(sprintf('%08s', $this->rem(319, 328, $detalhe)), 2) + array_fill(0, 5, '');
        if ($d->hasOcorrencia('06', '15', '16', '17')) {
            $this->totais['qtdLiquidados']++;
            $this->totais['vlrLiquidados'] += $d->getValorRecebido();
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02')) {
            $this->totais['qtdEntradas']++;
            $this->totais['vlrEntradas'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10')) {
            $this->totais['qtdBaixados']++;
            $this->totais['vlrBaixados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('23')) {
            $this->totais['qtdProtestados']++;
            $this->totais['vlrProtestados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14')) {
            $this->totais['qtdAlterados']++;
            $this->totais['vlrAlterados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '24', '27', '30', '32')) {
            $this->totais['qtdErros']++;
            if (isset($this->rejeicoes[$d->getOcorrencia()])) {
                $error = Util::appendStrings(
                    Arr::get($this->rejeicoes[$d->getOcorrencia()], $msgAdicional[0], ''),
                    Arr::get($this->rejeicoes[$d->getOcorrencia()], $msgAdicional[1], ''),
                    Arr::get($this->rejeicoes[$d->getOcorrencia()], $msgAdicional[2], ''),
                    Arr::get($this->rejeicoes[$d->getOcorrencia()], $msgAdicional[3], ''),
                    Arr::get($this->rejeicoes[$d->getOcorrencia()], $msgAdicional[4], '')
                );
            } else {
                $error = $d->getOcorrenciaDescricao();
            }
            $d->setError($error);
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
     * @throws \Exception
     */
    protected function processarTrailer(array $trailer)
    {
        $totais = $this->getTrailer()
            ->setQuantidadeEmCarteira((int) $this->rem(18, 25, $trailer))
            ->setQuantidadeTitulos((int) $this->totais['qtdTitulos'])
            ->setQuantidadeLiquidados((int) $this->totais['qtdLiquidados'])
            ->setQuantidadeEntradas((int) $this->totais['qtdEntradas'])
            ->setQuantidadeBaixados((int) $this->totais['qtdBaixados'])
            ->setQuantidadeAlterados((int) $this->totais['qtdAlterados'])
            ->setQuantidadeConfirmacaoInstrucaoProtestos((int) $this->totais['qtdProtestados'])
            ->setQuantidadeErros((int) $this->totais['qtdErros']);

        if ($this->usandoCentavos) {
            $totais->setValorEmCarteira((int) $this->rem(26, 39, $trailer))
                ->setValorTitulos((int) $this->totais['vlrTitulos'])
                ->setValorLiquidados((int) $this->totais['vlrLiquidados'])
                ->setValorEntradas((int) $this->totais['vlrEntradas'])
                ->setValorBaixados((int) $this->totais['vlrBaixados'])
                ->setValorAlterados((int) $this->totais['vlrAlterados'])
                ->setValorConfirmacaoInstrucaoProtestos((int) $this->totais['vlrProtestados']);
        } else {
            $totais->setValorEmCarteira((float) Util::nFloat($this->rem(26, 39, $trailer) / 100, 2, false))
                ->setValorTitulos((float) Util::nFloat($this->totais['vlrTitulos'] / 100, 2, false))
                ->setValorLiquidados((float) Util::nFloat($this->totais['vlrLiquidados'] / 100, 2, false))
                ->setValorEntradas((float) Util::nFloat($this->totais['vlrEntradas'] / 100, 2, false))
                ->setValorBaixados((float) Util::nFloat($this->totais['vlrBaixados'] / 100, 2, false))
                ->setValorAlterados((float) Util::nFloat($this->totais['vlrAlterados'] / 100, 2, false))
                ->setValorConfirmacaoInstrucaoProtestos((float) Util::nFloat($this->totais['vlrProtestados'] / 100, 2, false));
        }

        return true;
    }
}
