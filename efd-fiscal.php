<?php #8.2
###################################################################
# PROGRAMA PARA EXTRAIR O ESTOQUE DA EFD E CONVERTER EM CSV/Excel
#
# CodeBy FernadoLima <lima.r.nando@gmail.com>
# Version 0.1
# Date 2024-11-26
#

# Variaveis
$efd_in     = './fiscal/efd-in.txt';
$efd_out    = './fiscal/efd-out.txt';
$csv_out    = './fiscal/produtos.csv';
$efd_sep    = '|';
$csv_sep    = ';';
$csv_del    = '"';

$fp = fopen( $csv_out, 'w' );

# DADOS
$_PRO  = array(); // MATRIZ COM TODOS OS PRODUTOS
$_NCM = array(); // MATRIZ COM TODOS OS NCM

# Percorre as linhas da EFD ICMS/IPI
foreach ( file($efd_in) as $l => $linha) {
    // Explode na linha com o separador da EFD
    $_linha =  explode($efd_sep, $linha);

    $codigo = $_linha[2];
    $ncm    = "";
    $tipo   = array( "00" => "Mercadoria para revenda", "07" => "Uso/Consumo", "09" => "Ativo Imobilizado", "99" => "Outros" );

    // LINHA 0200 : Cadastro do Produtro 
    if ( $_linha[1] == '0200' ) {
        /* Estrutura EFD
        |0200|17264|MARCADOR RETROPROJ. 2MM PILOT AZ SM 1X1|7897424023516||56|00|96082000||96||18,00|1902800|
        [2] Código 
        [3] Descrição
        [4] Código de Barras
        [6] Unidade
        [7] Tipo de item : 00 - Revenda | 07 - Uso/Consumo | 09 - Ativo imobilizado   
        [8] NCM
        [10] NCM base/classe
        [12] Alíquota ICMS
        */

        
        // PRODUTO
        $_PRO[ $codigo ] = array(
            "codigo" => $codigo,
            "nome" => $_linha[3],
            "unidade" => "UN",//$_linha[6],
            "tipoSped" => $_linha[7],
            "tipo" => ( isset($tipo[$_linha[7]]) ? $tipo[$_linha[7]] : $tipo[99] ),
            "ncm" => $_linha[8],
            "icms" => $_linha[12],
            "quantidade" => 0, // quantidade
            "valor" => 0.00,
            "estoque" => 0.00 // valor do estoque total
        );

        // NCM
        $ncm = $_linha[8];
        if ( !isset($_NCM[ $ncm ]) ) { 
            $_NCM[$ncm] = 1;
        } else { $_NCM[$ncm] += 1; }   

    }    


    // LINHA H010 : Estoque do Produto 
    if ( $_linha[1] == 'H010' ) {
        /* Estrutura EFD
        |H010|17264|56|3,000|3,400000|10,20|0||||10,20|
        [2] Código 
        [3] Unidade
        [4] Quantidade
        [5] Valor unitário
        [6] Total do item
        */
        if ( isset($_PRO[$codigo]) ) {
            $_PRO[$codigo]['quantidade'] = intval($_linha[4]); 
            $_PRO[$codigo]['valor'] = $_linha[5];
            $_PRO[$codigo]['estoque'] = $_linha[6];
        }
    
    }    


}


### EXPORTACAO CSV
$cabecalho = array("Código", "Nome", "Unidade", "SPED", "Tipo", "NCM", "ICMS", "Quantidade", "Valor", "Estoque" );
$cab = '"' . implode('";"', $cabecalho) . '"';
fwrite($fp, strval($cab) . PHP_EOL );


foreach ( $_PRO as $_p ) {
    $escreve = '"' . implode('";"', $_p) . '"';
    fwrite($fp, strval($escreve) . PHP_EOL );
}


#print_r($_PRO);
#print_r($_NCM);
