<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Arr;

class Banrisul extends AbstractRetorno implements RetornoCnab400
{
    /**
     * Array com as ocorrencias do banco;
     *
     * @var array
     */
    private $ocorrencias = [
        '02' => 'Confirmação de entrada',
        '03' => 'Entrada rejeitada',
        '04' => 'Baixa de título liquidado por edital',
        '06' => 'Liquidação normal',
        '07' => 'Liquidação parcial',
        '08' => 'Baixa por pagamento, liquidação pelo saldo',
        '09' => 'Devolução automática',
        '10' => 'Baixado conforme instruções',
        '11' => 'Arquivo levantamento',
        '12' => 'Concessão de abatimento',
        '13' => 'Cancelamento de abatimento',
        '14' => 'Vencimento alterado',
        '15' => 'Pagamento em cartório',
        '16' => 'Alteração de dados',
        '18' => 'Alteração de instruções',
        '19' => 'Confirmação de instrução protesto',
        '20' => 'Confirmação de instrução para sustar protesto',
        '21' => 'Aguardando autorização para protesto por edital',
        '22' => 'Protesto sustado por alteração de vencimento e prazo de cartório',
        '23' => 'Confirmação da entrada em cartório',
        '25' => 'Devolução, liquidado anteriormente',
        '26' => 'Devolvido pelo cartório – erro de informação.',
        '30' => 'cobrança a creditar (liquidação em trânsito)',
        '31' => 'Título em trânsito pago em cartório',
        '32' => 'Reembolso e transferência Desconto e Vendor ou carteira em garantia',
        '33' => 'Reembolso e devolução Desconto e Vendor',
        '34' => 'Reembolso não efetuado por falta de saldo',
        '40' => 'Baixa de títulos protestados',
        '41' => 'Despesa de aponte.',
        '42' => 'Alteração de título',
        '43' => 'Relação de títulos',
        '44' => 'Manutenção mensal',
        '45' => 'Sustação de cartório e envio de título a cartório',
        '46' => 'Fornecimento de formulário pré-impresso',
        '47' => 'Confirmação de entrada – Pagador DDA',
        '68' => 'Acerto dos dados do rateio de crédito',
        '69' => 'Cancelamento dos dados do rateio',
    ];

    /**
     * Array com as possiveis descricoes de baixa e liquidacao.
     *
     * @var array
     */
    private $baixa_liquidacao = [
        '37' => 'Cancelamento de rateio por motivo de baixa comandada',
        '38' => 'Rateio efetuado, Beneficiário aguardando crédito',
        '39' => 'Rateio efetuado, Beneficiário já creditado',
        '40' => 'Rateio não efetuado, conta débito Beneficiário principal bloqueada',
        '41' => 'Rateio não efetuado, conta Beneficiário encerrada',
        '42' => 'Rateio não efetuado, código cálculo 2 (valor registro) e valor pago menor',
        '43' => 'Ocorrência não possui rateio',
    ];

    /**
     * Array com as possiveis rejeicoes do banco.
     *
     * @var array
     */
    private $rejeicoes = [
        '01' => 'Código do Banco inválido',
        '02' => 'Agência/Conta/Número de controle – Inválido Cobrança Partilhada',
        '04' => 'Código do movimento não permitido para a carteira',
        '05' => 'Código do movimento inválido',
        '07' => 'Título rejeitado na cobrança CEP irregular',
        '08' => 'Nosso Número inválido',
        '09' => 'Nosso Número duplicado',
        '10' => 'Carteira inválida',
        '15' => 'Características da cobrança incompatíveis – se a carteira e a moeda forem válidas e não existir espécie',
        '16' => 'Data de vencimento inválida',
        '17' => 'Data de vencimento anterior à data de emissão',
        '18' => 'Vencimento fora do prazo de operação',
        '20' => 'Valor do título inválido (não numérico)',
        '21' => 'Espécie do título inválida (arquivo de registro)',
        '23' => 'Aceite inválido – verifica conteúdo válido',
        '24' => 'Data de emissão inválida – verifica se a data é numérica e se está no formato válido',
        '25' => 'Data de emissão posterior à data de processamento',
        '26' => 'Código de juros de mora inválido',
        '27' => 'Valor/taxa de juros de mora inválido',
        '28' => 'Código do desconto inválido',
        '29' => 'Valor do desconto maior ou igual ao valor do título',
        '30' => 'Desconto a conceder não confere:',
        '32' => 'Valor de IOF inválido:',
        '33' => 'Valor do abatimento inválido – para registro de título verifica se o campo é numérico e para concessão/cancelamento de abatimento indica o erro',
        '34' => 'Valor do abatimento maior ou igual ao valor do título',
        '37' => 'Código para protesto inválido – rejeita o título se o campo for diferente de branco, 0, 1 ou 3',
        '38' => 'Prazo para protesto inválido – se o código for 1 verifica se o campo é numérico',
        '39' => 'Pedido de protesto não permitido para o título – não permite protesto para as carteiras R, S, N e X',
        '40' => 'Título com ordem de protesto emitida (para retorno de alteração)',
        '41' => 'Pedido de cancelamento/sustação de protesto inválido',
        '42' => 'Código para baixa/devolução ou instrução inválido – verifica se o código é branco, 0, 1 ou 2',
        '43' => 'Prazo para baixa/devolução inválido – se o código é 1 verifica se o campo prazo é numérico',
        '44' => 'Código da moeda inválido',
        '45' => 'Nome do Pagador inválido ou alteração do Pagador não permitida',
        '46' => 'Tipo/número de inscrição do Pagador inválido',
        '47' => 'Endereço não informado ou alteração de endereço não permitida',
        '48' => 'CEP inválido ou alteração de CEP não permitida',
        '49' => 'CEP sem praça de cobrança ou alteração de cidade não permitida',
        '50' => 'CEP referente a um Banco Correspondente',
        '52' => 'Unidade de Federação inválida ou alteração de UF não permitida',
        '53' => 'Tipo/Número de inscrição do Sacador/Avalista inválido',
        '54' => 'Sacador/Avalista não informado – para espécie AD o nome do Sacador é obrigatório',
        '57' => 'Código da multa inválido',
        '58' => 'Data da multa inválida',
        '59' => 'Valor/percentual da multa inválido',
        '60' => 'Movimento para título não cadastrado – alteração ou devolução',
        '62' => 'Tipo de impressão inválido – Segmento 3S',
        '63' => 'Entrada para título já cadastrado',
        '79' => 'Data de juros de mora inválido – valida data ou prazo na instrução de juros',
        '80' => 'Data do desconto inválida – valida data ou prazo da instrução de desconto',
        '81' => 'CEP inválido do Sacador',
        '83' => 'Tipo/Número de inscrição do Sacador inválido',
        '84' => 'Sacador não informado',
        '86' => 'Seu número inválido (para retorno de alteração)',
    ];

    /**
     * Código do banco
     *
     * @var string
     */
    protected $codigoBanco = BoletoContract::COD_BANCO_BANRISUL;

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
            ->setServico($this->rem(12, 19, $header))
            ->setAgencia($this->rem(27, 30, $header))
            ->setConta($this->rem(31, 39, $header))
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
        $d = $this->detalheAtual();

        $d->setCarteira($this->rem(108, 108, $detalhe))
            ->setNossoNumero($this->rem(63, 72, $detalhe))
            ->setNumeroDocumento($this->rem(117, 126, $detalhe))
            ->setNumeroControle((int) $this->rem(38, 62, $detalhe))
            ->setOcorrencia($this->rem(109, 110, $detalhe))
            ->setOcorrenciaDescricao(Arr::get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
            ->setDataOcorrencia($this->rem(111, 116, $detalhe))
            ->setDataVencimento($this->rem(147, 152, $detalhe))
            ->setDataCredito($this->rem(296, 301, $detalhe))
            ->setCodigoLiquidacao((int) $this->rem(343, 343, $detalhe))
            ->setLinhaRegistro($this->rem(395, 400, $detalhe));

        if ($this->usandoCentavos) {
            $d->setValor((int) $this->rem(153, 165, $detalhe))
                ->setValorTarifa((int) $this->rem(176, 188, $detalhe))
                ->setValorOutrasDespesas((int) $this->rem(189, 201, $detalhe))
                ->setValorAbatimento((int) $this->rem(228, 240, $detalhe))
                ->setValorDesconto((int) $this->rem(241, 253, $detalhe))
                ->setValorRecebido((int) $this->rem(254, 266, $detalhe))
                ->setValorMora((int) $this->rem(267, 279, $detalhe)) // banrisul usa juros e multa no mesmo campo
                ->setValorOutrasReceitas((int) $this->rem(280, 292, $detalhe));
        } else {
            $d->setValor((float) Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
                ->setValorTarifa((float) Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
                ->setValorOutrasDespesas((float) Util::nFloat($this->rem(189, 201, $detalhe) / 100, 2, false))
                ->setValorAbatimento((float) Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
                ->setValorDesconto((float) Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
                ->setValorRecebido((float) Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
                ->setValorMora((float) Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false)) // banrisul usa juros e multa no mesmo campo
                ->setValorOutrasReceitas((float) Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));
        }

        /**
         * ocorrencias
         */
        $msgAdicional = str_split(sprintf('%010s', $this->rem(383, 392, $detalhe)), 2) + array_fill(0, 5, '');
        if ($d->hasOcorrencia('04', '06', '07', '08', '15', '25')) {
            $this->totais['qtdLiquidados']++;
            $this->totais['vlrLiquidados'] += $d->getValorRecebido();
            $ocorrencia = Util::appendStrings(
                $d->getOcorrenciaDescricao(),
                Arr::get($this->baixa_liquidacao, $msgAdicional[0], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[1], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[2], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[3], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[4], '')
            );
            $d->setOcorrenciaDescricao($ocorrencia);
            $d->setOcorrenciaTipo($d::OCORRENCIA_LIQUIDADA);
        } elseif ($d->hasOcorrencia('02', '47')) {
            $this->totais['qtdEntradas']++;
            $this->totais['vlrEntradas'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_ENTRADA);
        } elseif ($d->hasOcorrencia('09', '10', '26', '40')) {
            $this->totais['qtdBaixados']++;
            $this->totais['vlrBaixados'] += $d->getValor();
            $ocorrencia = Util::appendStrings(
                $d->getOcorrenciaDescricao(),
                Arr::get($this->baixa_liquidacao, $msgAdicional[0], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[1], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[2], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[3], ''),
                Arr::get($this->baixa_liquidacao, $msgAdicional[4], '')
            );
            $d->setOcorrenciaDescricao($ocorrencia);
            $d->setOcorrenciaTipo($d::OCORRENCIA_BAIXADA);
        } elseif ($d->hasOcorrencia('23')) {
            $this->totais['qtdProtestados']++;
            $this->totais['vlrProtestados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_PROTESTADA);
        } elseif ($d->hasOcorrencia('14', '16', '18', '42')) {
            $this->totais['qtdAlterados']++;
            $this->totais['vlrAlterados'] += $d->getValor();
            $d->setOcorrenciaTipo($d::OCORRENCIA_ALTERACAO);
        } elseif ($d->hasOcorrencia('03', '24')) {
            $this->totais['qtdErros']++;
            $ocorrencia = Util::appendStrings(
                $d->getOcorrenciaDescricao() . ':',
                Arr::get($this->rejeicoes, $msgAdicional[0], ''),
                Arr::get($this->rejeicoes, $msgAdicional[1], ''),
                Arr::get($this->rejeicoes, $msgAdicional[2], ''),
                Arr::get($this->rejeicoes, $msgAdicional[3], ''),
                Arr::get($this->rejeicoes, $msgAdicional[4], '')
            );
            $d->setError($ocorrencia ?? 'Consulte seu Internet Banking');
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
