<?php

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;
use Exception;
use TCPDF;

// class PdfCarne extends AbstractPdf implements PdfContract
class PdfCarne extends TCPDF
{
    const OUTPUT_STANDARD = 'I';
    const OUTPUT_DOWNLOAD = 'D';
    const OUTPUT_SAVE = 'F';
    const OUTPUT_STRING = 'S';

    protected $orientacaoBoleto = 'L';

    protected $PadraoFont = 'helvetica';

    /**
     * @var BoletoContract[]
     */
    protected $boleto = [];

    /**
     * @var bool
     */
    protected $print = false;

    public $pageW = 210;
    public $pageH = 80;

    /**
     * @var bool
     */
    protected $showInstrucoes = true;

    protected $desc = 3; // tamanho célula descrição
    protected $cell = 4; // tamanho célula dado
    protected $fdes = 6; // tamanho fonte descrição
    protected $fcel = 8; // tamanho fonte célula
    protected $small = 0.2; // tamanho barra fina
    protected $totalBoletos = 0;

    public function __construct(
        $autoPageBreak = false,
        $marginLeft = 1,
        $marginTop = 1,
        $marginRight = 1,
        $orientacaoBoleto = 'L'
    ) {
        parent::__construct('L', 'mm', array('80', '297'));
        $this->SetAutoPageBreak($autoPageBreak);
        $this->SetLeftMargin($marginLeft);
        $this->SetTopMargin($marginTop);
        $this->SetRightMargin($marginRight);
        $this->SetLineWidth($this->small);
        $this->setOrientacaoBoleto($orientacaoBoleto);
        $this->setPrintFooter(false);
        $this->setPrintHeader(false);
    }

    public function setOrientacaoBoleto($orientacao = 'L')
    {
        if (!in_array($orientacao, ['L', 'P'])) {
            throw new Exception('Orientacao do boleto deve ser L ou P');
        }

        $this->orientacaoBoleto = $orientacao;
    }

    /**
     * @param integer $i
     *
     * @return $this
     */
    protected function instrucoes($i)
    {
        // $this->SetFont($this->PadraoFont, '', 8);
        // if ($this->totalBoletos > 1) {
        //     $this->SetAutoPageBreak(true);
        //     $this->SetY(5);
        //     $this->Cell(30, 10, date('d/m/Y H:i:s'));
        //     $this->Cell(0, 10, "Boleto " . ($i + 1) . " de " . $this->totalBoletos, 0, 1, 'R');
        // }

        // $this->SetFont($this->PadraoFont, 'B', 8);
        // if ($this->showInstrucoes) {
        //     $this->Cell(0, 5, $this->_('Instruções de Impressão'), 0, 1, 'C');
        //     $this->Ln(5);
        //     $this->SetFont($this->PadraoFont, '', 6);
        //     if (count($this->boleto[$i]->getInstrucoesImpressao()) > 0) {
        //         $this->listaLinhas($this->boleto[$i]->getInstrucoesImpressao(), 0);
        //     } else {
        //         $this->Cell(0, $this->desc, $this->_('- Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).'), 0, 1, 'L');
        //         $this->Cell(0, $this->desc, $this->_('- Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.'), 0, 1, 'L');
        //         $this->Cell(0, $this->desc, $this->_('- Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.'), 0, 1, 'L');
        //         $this->Cell(0, $this->desc, $this->_('- Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.'), 0, 1, 'L');
        //         $this->Cell(0, $this->desc, $this->_('- Caso tenha problemas ao imprimir, copie a seqüencia numérica abaixo e pague no caixa eletrônico ou no internet banking:'), 0, 1, 'L');
        //     }
        //     $this->Ln(4);

        //     $this->SetFont($this->PadraoFont, '', $this->fcel);
        //     $this->Cell(25, $this->cell, $this->_('Linha Digitável: '), 0, 0);
        //     $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        //     $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getLinhaDigitavel()), 0, 1);
        //     $this->SetFont($this->PadraoFont, '', $this->fcel);
        //     $this->Cell(25, $this->cell, $this->_('Número: '), 0, 0);
        //     $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        //     $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getNumero()), 0, 1);
        //     $this->SetFont($this->PadraoFont, '', $this->fcel);
        //     $this->Cell(25, $this->cell, $this->_('Valor: '), 0, 0);
        //     $this->SetFont($this->PadraoFont, 'B', $this->fcel);
        //     $this->Cell(0, $this->cell, $this->_(Util::nReal($this->boleto[$i]->getValor())), 0, 1);
        //     $this->SetFont($this->PadraoFont, '', $this->fcel);
        // }

        // $this->traco('Recibo do Pagador', 4);
        // return $this;
    }


    /**
     * @param integer $i
     *
     * @return $this
     */
    protected function logoEmpresa($i)
    {
        $this->Ln(2);
        $this->SetFont($this->PadraoFont, '', $this->fdes);

        $logo = preg_replace('/\&.*/', '', $this->boleto[$i]->getLogo());
        $ext = pathinfo($logo, PATHINFO_EXTENSION);

        if ($this->boleto[$i]->getLogo() && !empty($this->boleto[$i]->getLogo())) {
            $this->Image($this->boleto[$i]->getLogo(), 20, ($this->GetY()), 0, 12, $ext);
        }
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getNome()), 0, 1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getDocumento(), '##.###.###/####-##'), 0, 1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getEndereco()), 0, 1);
        $this->Cell(56);
        $this->Cell(0, $this->desc, $this->_($this->boleto[$i]->getBeneficiario()->getCepCidadeUf()), 0, 1);
        $this->Ln(8);

        return $this;
    }

    /**
     * @param integer $i
     *
     * @return $this
     */
    // tomar cuidado ao mexer nisto, mto do que se faz por estar rotacionado inverte o X e o Y das celulas
    protected function Boleto($i)
    {
        $maxW = $this->w - $this->lMargin - $this->rMargin;
        $maxH = $this->h - $this->tMargin - $this->bMargin;
        $h = 2.5;
        $wCanhoto = $maxW * .2; // coluna 1 canhoto
        $wMiddleMargin = $maxW * .01; // coluna 2 margen meio
        $wBillet = $maxW - $wCanhoto - $wMiddleMargin; // coluna 3 boleto


        $fontHeader = 5;
        $fontContent = 6.11;

        $bHeader = 'TRL';
        $bContent = 'BRL';

        $this->AddPage('P');
        $this->StartTransform();
        $this->Rotate(-90, 40, 40);

        $instrucoes = $this->boleto[$i]->getInstrucoes();

        $i1 = $instrucoes[0] ? $instrucoes[0] : '';
        $i2 = $instrucoes[1] ? $instrucoes[1] : '';
        $i3 = $instrucoes[2] ? $instrucoes[2] : '';
        $i4 = $instrucoes[3] ? $instrucoes[3] : '';
        $i5 = $instrucoes[4] ? $instrucoes[4] : '';

        // quadrado de fundo
        $this->SetFillColor(255, 255, 255);
        $this->setXY($this->lMargin, $this->tMargin);
        $this->cell(
            $this->h - ($this->tMargin  * 2),
            $this->w - $this->lMargin - $this->rMargin,
            '',
            '',
            0,
            '',
            true
        );
        // fim quadrado de fondo
        $this->setXY($this->lMargin, $this->tMargin);
        $this->setCellPaddings($left = 1, $top = -1, $right = 1, $bottom = -1);

        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto * .5, $h, '', '');  // logo empresa
        $this->cell($wCanhoto * .5, $h, 'Banco', 'LR');
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .15, $h, ''); // logo banco
        $this->cell($wBillet * .15, $h, 'Banco', 'LR');
        $this->cell($wBillet * .7, $h, 'Linha Digitável', 'LR');
        $this->ln();
        // linha 1 valores
        $this->SetFont($this->PadraoFont, 'B', 15);
        $this->Image(
            public_path('images/logo.jpeg'),
            $this->getX() + 2,
            ($this->GetY() - 1),
            15
        );
        $this->cell($wCanhoto * .5, $h, '', ''); // logo empresa
        $this->cell($wCanhoto * .5, $h, $this->boleto[$i]->getCodigoBanco(), $bContent,);
        $this->cell($wMiddleMargin, $h, '');
        $this->Image(
            $this->boleto[$i]->getLogoBanco(),
            $this->getX(),
            ($this->GetY() - 2),
            28
        );
        $this->cell($wBillet * .15, $h, ''); // linha 2 logo banco
        $this->cell($wBillet * .15, $h, $this->boleto[$i]->getCodigoBanco(), $bContent);
        $this->cell($wBillet * .7, $h, $this->boleto[$i]->getLinhaDigitavel(), $bContent);
        $this->ln();

        // linha 2 headers
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto * .5, $h, 'Parcela', $bHeader);
        $this->cell($wCanhoto * .5, $h, 'Vencimento', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, 'Local de Pagamento', $bHeader);
        $this->cell($wBillet * .2, $h, 'Vencimento', $bHeader);
        $this->ln();
        // linha 2 valores
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto * .5, $h, $this->boleto[$i]->getNumeroParcela(), $bContent);
        $this->cell($wCanhoto * .5, $h, $this->boleto[$i]->getDataVencimento()->format('d/m/Y'), $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, 'Pagável em qualquer banco até a data de vencimento.', $bContent);
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getDataVencimento()->format('d/m/Y'), $bContent);
        $this->ln();

        // linha 3 headers
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, 'Agência / Cod. Beneficiário', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .6, $h, 'Beneficiário', $bHeader);
        $this->cell($wBillet * .2, $h, 'CPF/CNPJ', $bHeader);
        $this->cell($wBillet * .2, $h, 'Agência / Cod. Beneficiário', $bHeader);
        $this->ln();
        // linha 3 content
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, $this->boleto[$i]->getAgenciaCodigoBeneficiario(), $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .6, $h, $this->boleto[$i]->getBeneficiario()->getNome(), $bContent);
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getBeneficiario()->getDocumento(), $bContent);
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getAgenciaCodigoBeneficiario(), $bContent);
        $this->ln();

        // linha 4
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, 'Nosso Número', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .2, $h, 'Data Documento', $bHeader);
        $this->cell($wBillet * .2, $h, 'Número Documento', $bHeader);
        $this->cell($wBillet * .1, $h, 'Espécie Doc.', $bHeader);
        $this->cell($wBillet * .1, $h, 'Aceite', $bHeader);
        $this->cell($wBillet * .2, $h, 'Data Processamento', $bHeader);
        $this->cell($wBillet * .2, $h, 'Nosso Número', $bHeader);
        $this->ln();
        // linha 4 content
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, $this->boleto[$i]->getNossoNumeroBoleto(), $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getDataProcessamento()->format('d/m/Y'), $bContent);
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getNumeroDocumento(), $bContent);
        $this->cell($wBillet * .1, $h, $this->boleto[$i]->getEspecieDoc(), $bContent);
        $this->cell($wBillet * .1, $h, $this->boleto[$i]->getAceite(), $bContent);
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getDataProcessamento()->format('d/m/Y'), $bContent);
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getNossoNumeroBoleto(), $bContent);
        $this->ln();

        // linha 5
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto * .5, $h, 'Número Doc.', $bHeader);
        $this->cell($wCanhoto * .5, $h, 'Espécie Doc.', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .2, $h, 'Uso Banco', $bHeader);
        $this->cell($wBillet * .1, $h, 'Carteira', $bHeader);
        $this->cell($wBillet * .1, $h, 'Espécie', $bHeader);
        $this->cell($wBillet * .2, $h, 'Quantidade', $bHeader);
        $this->cell($wBillet * .2, $h, 'Valor', $bHeader);
        $this->cell($wBillet * .2, $h, '(=) Valor Documento', $bHeader);
        $this->ln();
        // linha 5 content
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto * .5, $h, $this->boleto[$i]->getNumeroDocumento(), $bContent);
        $this->cell($wCanhoto * .5, $h, $this->boleto[$i]->getEspecieDoc(), $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->cell($wBillet * .1, $h, $this->boleto[$i]->getCarteiraNome(), $bContent);
        $this->cell($wBillet * .1, $h, $this->boleto[$i]->getEspecieDoc(), $bContent);
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->cell($wBillet * .2, $h, Util::nReal($this->boleto[$i]->getValor()), $bContent);
        $this->ln();

        // linha 6
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, '(=) Valor Documento', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, 'Instruções', 'LR');
        $this->cell($wBillet * .2, $h, '(-) Desconto / Abatimento', $bHeader);
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, Util::nReal($this->boleto[$i]->getValor()), $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, $i1, 'LR');
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->ln();
        // linha 7
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, '(-) Desconto / Abatimento', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'LR');
        $this->cell($wBillet * .2, $h, '(-) Outras Deduções', $bHeader);
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, '', $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, $i2, 'LR');
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->ln();

        // linha 8
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, '(-) Outras Deduções', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'LR');
        $this->cell($wBillet * .2, $h, '(+) Mora / Multa ', $bHeader);
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, '', $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, $i3, 'LR');
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->ln();

        // linha 8
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, '(+) Mora / Multa', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'LR');
        $this->cell($wBillet * .2, $h, '(+) Outros Acrécimos', $bHeader);
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, '', $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, $i4, 'LR');
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->ln();

        // linha 8
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, '(+) Outros Acrécimos', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'LR');
        $this->cell($wBillet * .2, $h, '(=) Valor Cobrado ', $bHeader);
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, '', $bContent);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, $i5, 'LR');
        $this->cell($wBillet * .2, $h, '', $bContent);
        $this->ln();

        // linha 9
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, '(=) Valor Cobrado', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, 'Pagador', 'TRL');
        $this->cell($wBillet * .2, $h, 'CPF/CNPJ', $bHeader);
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, '', $bContent); //valor cobrado
        $this->cell($wMiddleMargin, $h, ''); // margen
        $this->cell($wBillet * .8, $h, $this->boleto[$i]->getPagador()->getNome(), 'LR'); // pagador
        $this->cell($wBillet * .2, $h, $this->boleto[$i]->getPagador()->getDocumento(), 'LR'); // cpf /cnpj
        $this->ln();

        // linha 10
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, 'Pagador', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, $this->boleto[$i]->getPagador()->getEnderecoCompleto(), 'LRB');
        $this->cell($wBillet * .2, $h, '', 'LRB');
        $this->ln();
        // linha 10 contents
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, $this->boleto[$i]->getPagador()->getNome(), $bContent); // pagador
        $this->cell($wMiddleMargin, $h, ''); // margen
        $this->cell($wBillet * .8, $h, 'Sacador / Avalista', 'L'); // vasio
        $this->cell($wBillet * .2, $h, 'Autenticação Mecânica', 'R'); // vasio
        $this->ln();

        // linha 11
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, 'Beneficiário', $bHeader);
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'L');
        $this->cell($wBillet * .2, $h, '', 'R');
        $this->ln();
        // linha 11 contents
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, $this->boleto[$i]->getBeneficiario()->getNome(), 'RL');
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'L'); // onde escreve o codigo de barras
        $this->cell($wBillet * .2, $h, '', 'R');
        $this->ln();

        // linha 12
        $this->SetFont($this->PadraoFont, 'B', $fontHeader);
        $this->cell($wCanhoto, $h, $this->boleto[$i]->getBeneficiario()->getEnderecoCompleto(), 'RL');
        $this->cell($wMiddleMargin, $h, '');
        $this->cell($wBillet * .8, $h, '', 'L');
        $this->cell($wBillet * .2, $h, '', 'R');
        $this->ln();
        $this->SetFont($this->PadraoFont, 'B', $fontContent);
        $this->cell($wCanhoto, $h, '', 'RLB');
        $this->cell($wMiddleMargin, $h, ''); // margens
        $xBarcode = $this->getX();
        $yBarcode = $this->getY();
        $this->cell($wBillet * .8, $h, '', 'LB');
        $this->cell($wBillet * .2, $h, '', 'RB');
        $this->ln();

        $this->write1DBarcode(
            $code = $this->boleto[$i]->getCodigoBarras(),
            $type = 'I25',
            $x = $xBarcode + 1,
            $y = $yBarcode - 7.5,
            $w = 100,
            $h = $h + 7,
            // $xres = 1,
            // $style = '',
            // $align = 'N'
        );

        // $this->Output('a.pdf', 'I');
        return $this;
    }

    /**
     * @param string $texto
     * @param integer $ln
     * @param integer $ln2
     * @param $posicaoTexto
     * @param $alinhamentoTexto
     * @param $tamanho
     */
    protected function traco($texto, $ln = null, $ln2 = null, $posicaoTexto = 1, $alinhamentoTexto = 'R', $tamanho = 261)
    {
        if ($ln == 1 || $ln) {
            $this->Ln($ln);
        }
        $this->SetFont($this->PadraoFont, '', $this->fdes);
        if ($texto && $posicaoTexto !== -1) {
            $this->Cell(0, 2, $this->_($texto), 0, 1, $alinhamentoTexto);
        }
        $this->Cell(0,  2, str_pad('-', $tamanho, ' -', STR_PAD_RIGHT), 0, 1);
        if ($texto && $posicaoTexto === -1) {
            $this->Cell(0, 2, $this->_($texto), 0, 1, $alinhamentoTexto);
        }
        if ($ln2 == 1 || $ln2) {
            $this->Ln($ln2);
        }
    }

    /**
     * @param integer $i
     */
    protected function codigoBarras($i)
    {
        // return $this;
        // $this->Ln(3);
        // $this->Cell(0, 15, '', 0, 1, 'L');
        // $this->i25($this->GetX(), $this->GetY() - 15, $this->boleto[$i]->getCodigoBarras(), 1, 17);
    }

    /**
     * Addiciona o boletos
     *
     * @param array $boletos
     * @param bool $withGroup
     *
     * @return $this
     */
    public function addBoletos(array $boletos, $withGroup = true)
    {
        if ($withGroup) {
            $this->StartPageGroup();
        }

        foreach ($boletos as $boleto) {
            $this->addBoleto($boleto);
        }

        return $this;
    }

    /**
     * Addiciona o boleto
     *
     * @param BoletoContract $boleto
     *
     * @return $this
     */
    public function addBoleto(BoletoContract $boleto)
    {
        $this->totalBoletos += 1;
        $this->boleto[] = $boleto;
        return $this;
    }

    /**
     * @return $this
     */
    public function hideInstrucoes()
    {
        // $this->showInstrucoes = false;
        // return $this;
    }

    /**
     * @return $this
     */
    public function showPrint()
    {
        $this->print = true;
        return $this;
    }

    /**
     * função para gerar o boleto
     *
     * @param string $dest tipo de destino const BOLETOPDF_DEST_STANDARD | BOLETOPDF_DEST_DOWNLOAD | BOLETOPDF_DEST_SAVE | BOLETOPDF_DEST_STRING
     * @param null $save_path
     *
     * @return string
     * @throws \Exception
     */
    public function gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null, $nameFile = null, $orientacao = 'L')
    {
        if ($this->totalBoletos == 0) {
            throw new \Exception('Nenhum Boleto adicionado');
        }

        for ($i = 0; $i < $this->totalBoletos; $i++) {
            $this->SetDrawColor('0', '0', '0');
            $this->Boleto($i);
        }

        if ($dest == self::OUTPUT_SAVE) {
            $this->Output($save_path, $dest, $this->print);
            return $save_path;
        }

        if ($nameFile == null) {
            $nameFile = Str::random(32);
        }

        return $this;
    }

    /**
     * @param $lista
     * @param integer $pulaLinha
     *
     * @return int
     */
    protected function listaLinhas($lista, $pulaLinha)
    {
        // foreach ($lista as $d) {
        //     $pulaLinha -= 2;
        //     $this->MultiCell(0, $this->cell - 0.2, $this->_(preg_replace('/(%)/', '%$1', $d)), 0, 1);
        // }

        // return $pulaLinha;
    }
}
