<?php

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Render\Pdf as PdfContract;
use Eduardokum\LaravelBoleto\Util;
use Illuminate\Support\Str;
use Exception;

class PdfCarne extends AbstractPdf implements PdfContract
{
    const OUTPUT_STANDARD = 'I';
    const OUTPUT_DOWNLOAD = 'D';
    const OUTPUT_SAVE = 'F';
    const OUTPUT_STRING = 'S';

    protected $orientacaoBoleto = 'L';

    protected $PadraoFont = 'Arial';

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
        $marginLeft = 2,
        $marginTop = 2,
        $marginRight = 2,
        $orientacaoBoleto = 'L'
    ) {
        // parent::__construct('P', 'mm', 'A4');
        parent::__construct('L', 'mm', array('80','210'));
        $this->SetAutoPageBreak($autoPageBreak);
        $this->SetLeftMargin($marginLeft);
        $this->SetTopMargin($marginTop);
        $this->SetRightMargin($marginRight);
        $this->SetLineWidth($this->small);
        $this->setOrientacaoBoleto($orientacaoBoleto);
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
        $this->SetFont($this->PadraoFont, '', 8);
        if ($this->totalBoletos > 1) {
            $this->SetAutoPageBreak(true);
            $this->SetY(5);
            $this->Cell(30, 10, date('d/m/Y H:i:s'));
            $this->Cell(0, 10, "Boleto " . ($i + 1) . " de " . $this->totalBoletos, 0, 1, 'R');
        }

        $this->SetFont($this->PadraoFont, 'B', 8);
        if ($this->showInstrucoes) {
            $this->Cell(0, 5, $this->_('Instruções de Impressão'), 0, 1, 'C');
            $this->Ln(5);
            $this->SetFont($this->PadraoFont, '', 6);
            if (count($this->boleto[$i]->getInstrucoesImpressao()) > 0) {
                $this->listaLinhas($this->boleto[$i]->getInstrucoesImpressao(), 0);
            } else {
                $this->Cell(0, $this->desc, $this->_('- Imprima em impressora jato de tinta (ink jet) ou laser em qualidade normal ou alta (Não use modo econômico).'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Utilize folha A4 (210 x 297 mm) ou Carta (216 x 279 mm) e margens mínimas à esquerda e à direita do formulário.'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Corte na linha indicada. Não rasure, risque, fure ou dobre a região onde se encontra o código de barras.'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Caso não apareça o código de barras no final, clique em F5 para atualizar esta tela.'), 0, 1, 'L');
                $this->Cell(0, $this->desc, $this->_('- Caso tenha problemas ao imprimir, copie a seqüencia numérica abaixo e pague no caixa eletrônico ou no internet banking:'), 0, 1, 'L');
            }
            $this->Ln(4);

            $this->SetFont($this->PadraoFont, '', $this->fcel);
            $this->Cell(25, $this->cell, $this->_('Linha Digitável: '), 0, 0);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);
            $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getLinhaDigitavel()), 0, 1);
            $this->SetFont($this->PadraoFont, '', $this->fcel);
            $this->Cell(25, $this->cell, $this->_('Número: '), 0, 0);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);
            $this->Cell(0, $this->cell, $this->_($this->boleto[$i]->getNumero()), 0, 1);
            $this->SetFont($this->PadraoFont, '', $this->fcel);
            $this->Cell(25, $this->cell, $this->_('Valor: '), 0, 0);
            $this->SetFont($this->PadraoFont, 'B', $this->fcel);
            $this->Cell(0, $this->cell, $this->_(Util::nReal($this->boleto[$i]->getValor())), 0, 1);
            $this->SetFont($this->PadraoFont, '', $this->fcel);
        }

        $this->traco('Recibo do Pagador', 4);
        return $this;
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
     *x
     * @return $this
     */
    protected function Boleto($i)
    {
        $maxW = $this->w - $this->lMargin - $this->rMargin;
        $w1 = $maxW * .2; // coluna 1 canhoto
        $w2 = $maxW * .01; // coluna 2 margen meio
        $w3 = $maxW - $w1 - $w2; // coluna 3 boleto
        $maxH = $this->h;

        $bHeader = 'TRL';
        $bContent = 'BRL';
        // $fontSizeH = 3;
        // $fontSizeC = 7;
        $instrucoes = $this->boleto[$i]->getInstrucoes();
        $i1 = $instrucoes[0] ? mb_convert_encoding($instrucoes[0], 'ISO-8859-1', 'UTF-8') : '';
        $i2 = $instrucoes[1] ? mb_convert_encoding($instrucoes[1], 'ISO-8859-1', 'UTF-8') : '';
        $i3 = $instrucoes[2] ? mb_convert_encoding($instrucoes[2], 'ISO-8859-1', 'UTF-8') : '';
        $i4 = $instrucoes[3] ? mb_convert_encoding($instrucoes[3], 'ISO-8859-1', 'UTF-8') : '';
        $i5 = $instrucoes[4] ? mb_convert_encoding($instrucoes[4], 'ISO-8859-1', 'UTF-8') : '';
        $h = 2.7;

        // dd(mb_convert_encoding($i1,'HTML-ENTITIES','ISO-8859-1'));

        // linha 1 headers
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1 * .5, $h, '');  // logo empresa
        $this->cell($w1 * .5, $h, 'Banco', 'LR');
        $this->cell($w2, $h, '');
        $this->cell($w3 * .15, $h, ''); // logo banco
        $this->cell($w3 * .15, $h, 'Banco', 'LR');
        $this->cell($w3 * .7, $h, $this->_('Linha Digitável'), 'LR');
        $this->ln();

        // linha 1 valores
        $this->SetFont('Arial', 'B', 7);
        $this->Image(
            public_path('images/logo.jpeg'),
            $this->getX() + 2,
            ($this->GetY() - 2),
            10
        );
        $this->cell($w1 * .5, $h, ''); // logo empresa
        $this->cell($w1 * .5, $h, $this->boleto[$i]->getCodigoBanco(), $bContent);
        $this->cell($w2, $h, '');
        $this->Image(
            $this->boleto[$i]->getLogoBanco(),
            $this->getX() - 1.8,
            ($this->GetY() - 4 ),
            25
        );
        $this->cell($w3 * .15, $h, ''); // linha 2 logo banco
        $this->cell($w3 * .15, $h, $this->boleto[$i]->getCodigoBanco(), $bContent);
        $this->cell($w3 * .7, $h, $this->boleto[$i]->getLinhaDigitavel(), $bContent);
        $this->ln();

        // linha 2 headers
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1 * .5, $h, 'Parcela', $bHeader);
        $this->cell($w1 * .5, $h, 'Vencimento', $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, 'Local de Pagamento', $bHeader);
        $this->cell($w3 * .2, $h, 'Vencimento', $bHeader);
        $this->ln();
        // linha 2 valores
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1 * .5, $h, '_parcela_', $bContent);
        $this->cell($w1 * .5, $h, $this->boleto[$i]->getDataVencimento()->format('d/m/Y'), $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $this->_('Pagável em qualquer banco até a data de vencimento.'), $bContent);
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getDataVencimento()->format('d/m/Y'), $bContent);
        $this->ln();

        // linha 3 headers
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, $this->_('Agência / Cod. Beneficiário'), $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .6, $h, $this->_('Beneficiário'), $bHeader);
        $this->cell($w3 * .2, $h, 'CPF/CNPJ', $bHeader);
        $this->cell($w3 * .2, $h, $this->_('Agência / Cod. Beneficiário'), $bHeader);
        $this->ln();
        // linha 3 content
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, $this->boleto[$i]->getAgenciaCodigoBeneficiario(), $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .6, $h, $this->boleto[$i]->getBeneficiario()->getNome(), $bContent);
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getBeneficiario()->getDocumento(), $bContent);
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getAgenciaCodigoBeneficiario(), $bContent);
        $this->ln();

        // linha 4
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, $this->_('Nosso Número'), $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .2, $h, 'Data Documento', $bHeader);
        $this->cell($w3 * .2, $h, $this->_('Número Documento'), $bHeader);
        $this->cell($w3 * .1, $h, $this->_('Espécie Doc.'), $bHeader);
        $this->cell($w3 * .1, $h, 'Aceite', $bHeader);
        $this->cell($w3 * .2, $h, 'Data Processamento', $bHeader);
        $this->cell($w3 * .2, $h, $this->_('Nosso Número'), $bHeader);
        $this->ln();
        // linha 4 content
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, $this->boleto[$i]->getNossoNumeroBoleto(), $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getDataProcessamento()->format('d/m/Y'), $bContent);
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getNumeroDocumento(), $bContent);
        $this->cell($w3 * .1, $h, $this->boleto[$i]->getEspecieDoc(), $bContent);
        $this->cell($w3 * .1, $h, $this->boleto[$i]->getAceite(), $bContent);
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getDataProcessamento()->format('d/m/Y'), $bContent);
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getNossoNumeroBoleto(), $bContent);
        $this->ln();

        // linha 5
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1 * .5, $h, $this->_('Número Doc.'), $bHeader);
        $this->cell($w1 * .5, $h, $this->_('Espécie Doc.'), $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .2, $h, 'Uso Banco', $bHeader);
        $this->cell($w3 * .1, $h, 'Carteira', $bHeader);
        $this->cell($w3 * .1, $h, $this->_('Espécie'), $bHeader);
        $this->cell($w3 * .2, $h, 'Quantidade', $bHeader);
        $this->cell($w3 * .2, $h, 'Valor', $bHeader);
        $this->cell($w3 * .2, $h, '(=) Valor Documento', $bHeader);
        $this->ln();
        // linha 5 content
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1 * .5, $h, $this->boleto[$i]->getNumeroDocumento(), $bContent);
        $this->cell($w1 * .5, $h, $this->boleto[$i]->getEspecieDoc(), $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->cell($w3 * .1, $h, $this->boleto[$i]->getCarteiraNome(), $bContent);
        $this->cell($w3 * .1, $h, $this->boleto[$i]->getEspecieDoc(), $bContent);
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->cell($w3 * .2, $h, Util::nReal($this->boleto[$i]->getValor()), $bContent);
        $this->ln();

        // linha 6
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, '(=) Valor Documento', $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $this->_('Instruções'), 'LR');
        $this->cell($w3 * .2, $h, '(-) Desconto / Abatimento', $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, Util::nReal($this->boleto[$i]->getValor()), $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $i1, 'LR');
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->ln();
        // linha 7
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, '(-) Desconto / Abatimento', $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', 'LR');
        $this->cell($w3 * .2, $h, $this->_('(-) Outras Deduções'), $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, '', $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $i2, 'LR');
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->ln();

        // linha 8
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, $this->_('(-) Outras Deduções'), $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', 'LR');
        $this->cell($w3 * .2, $h, '(+) Mora / Multa ', $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, '', $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $i3, 'LR');
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->ln();

        // linha 8
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, '(+) Mora / Multa', $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', 'LR');
        $this->cell($w3 * .2, $h, $this->_('(+) Outros Acrécimos'), $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, '', $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $i4, 'LR');
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->ln();

        // linha 8
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, $this->_('(+) Outros Acrécimos'), $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', 'LR');
        $this->cell($w3 * .2, $h, '(=) Valor Cobrado ', $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, '', $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $i5, 'LR');
        $this->cell($w3 * .2, $h, '', $bContent);
        $this->ln();

        // linha 9
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, '(=) Valor Cobrado', $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, 'Pagador', 'TRL');
        $this->cell($w3 * .2, $h, 'CPF/CNPJ', $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, '', $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, $this->boleto[$i]->getPagador()->getNome(), 'LR');
        $this->cell($w3 * .2, $h, $this->boleto[$i]->getPagador()->getDocumento(), $bContent);
        $this->ln();

        // linha 10
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, 'Pagador', $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3, $h, 'Sacador / Avalista', $bHeader);
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, $this->boleto[$i]->getPagador()->getNome(), $bContent);
        $this->cell($w2, $h, '');
        $this->cell($w3, $h, '', $bContent);
        $this->ln();

        // linha 11
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, $this->_('Beneficiário'), $bHeader);
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', $bHeader);
        $this->cell($w3 * .2, $h, $this->_('Autenticação Mecânica'), 'RL');
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, $this->boleto[$i]->getBeneficiario()->getNome(), 'RL');
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', 'RL');
        $this->cell($w3 * .2, $h, '', 'RL');
        $this->ln();

        // linha 12
        $this->SetFont('Arial', 'B', 3);
        $this->cell($w1, $h, '', 'RL');
        $this->cell($w2, $h, '');
        $this->cell($w3 * .8, $h, '', 'RL');
        $this->cell($w3 * .2, $h, '', 'RL');
        $this->ln();
        $this->SetFont('Arial', 'B', 5);
        $this->cell($w1, $h, '', 'RLB');
        $this->cell($w2, $h, '');
        $this->i25(
            $this->GetX() + 2,
            $this->GetY() - 7,
            $this->boleto[$i]->getCodigoBarras(),
            .93,
            $h + 7
        );
        $this->cell($w3 * .8, $h, '', 'RL');
        $this->cell($w3 * .2, $h, '', $bContent);

        $this->ln();
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
        $this->Cell(0, 2, str_pad('-', $tamanho, ' -', STR_PAD_RIGHT), 0, 1);
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
        return $this;
        $this->Ln(3);
        $this->Cell(0, 15, '', 0, 1, 'L');
        $this->i25($this->GetX(), $this->GetY() - 15, $this->boleto[$i]->getCodigoBarras(), 1, 17);
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
        $this->showInstrucoes = false;
        return $this;
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
    public function gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null, $nameFile = null)
    {
        if ($this->totalBoletos == 0) {
            throw new Exception('Nenhum Boleto adicionado');
        }

        for ($i = 0; $i < $this->totalBoletos; $i++) {
            $this->SetDrawColor('0', '0', '0');
            $this->AddPage();
            $this->Boleto($i)->codigoBarras($i);
        }
        if ($dest == self::OUTPUT_SAVE) {
            $this->Output($save_path, $dest, $this->print);
            return $save_path;
        }
        if ($nameFile == null) {
            $nameFile = Str::random(32);
        }

        return $this->Output($nameFile . '.pdf', $dest = 'I', $this->print);
    }

    /**
     * @param $lista
     * @param integer $pulaLinha
     *
     * @return int
     */
    protected function listaLinhas($lista, $pulaLinha)
    {
        foreach ($lista as $d) {
            $pulaLinha -= 2;
            $this->MultiCell(0, $this->cell - 0.2, $this->_(preg_replace('/(%)/', '%$1', $d)), 0, 1);
        }

        return $pulaLinha;
    }
}
