/*
    This are the tests written for the encrypt and decrypt function for the gene class in gene.class.php
 */
#!/usr/bin/php
<?php

include('./xgene360/includes/classes/simulator/gene.class.php');
echo " Test Suite for Gene ";
echo "\n";
echo " Test 1 ";
$gene1 = new Gene();
$input_gene = "01000001";
$gene1->str_gene = $input_gene;
$r1 = $gene1->encrypt();
$gene1->decrypt($r1);
echo "\n";
echo " Are they the same? ". ($gene1->str_gene === $input_gene);

echo "\n";

echo " Test 2 "; 
$gene1 = new Gene();
$input_gene = "01000010";
$gene1->str_gene = $input_gene;
$r1 = $gene1->encrypt();
$gene1->decrypt($r1);
echo "\n";
echo " Are they the same? ". ($gene1->str_gene === $input_gene);
echo "\n";

echo " Test 3 "; 
$gene1 = new Gene();
$gene1->create(1, true);
$r1 = $gene1->encrypt();
$gene1->decrypt($r1);
echo "\n";
echo " Are they the same? ". ($gene1->str_gene === "00000011");
echo "\n";


echo " Test 4 "; 
$gene1 = new Gene();
$gene1->create(1, false);
$r1 = $gene1->encrypt();
$gene1->decrypt($r1);
echo "\n";
echo " Are they the same? ". ($gene1->str_gene === "00000000");
echo "\n";

echo " Test 5 "; 
$gene1 = new Gene();
$gene1->create(3, true);
$r1 = $gene1->encrypt();
$gene1->decrypt($r1);
echo "\n";
echo " Are they the same? ". ($gene1->str_gene === "11111100");
echo "\n";

echo " SandBox ";
/*
echo "\n";
$gene1 = new Gene();
$gene1->str_gene = "11111111";
echo "encrypting to base sixteen: ".$gene1->encrypt();
echo "\n";

echo "\n";
$gene1 = new Gene();
$gene1->str_gene = "00010000";
echo "encrypting to base sixteen: ".$gene1->encrypt();
echo "\n";

echo "\n";
$gene1 = new Gene();
$gene1->str_gene = "00001000";
echo "encrypting to base sixteen: ".$gene1->encrypt();
echo "\n";
 
echo "\n";
$gene1 = new Gene();
$gene1->str_gene = "00000000";
echo "encrypting to base sixteen: ".$gene1->encrypt();
echo "\n";

echo "\n";
$gene1 = new Gene();
$gene1->str_gene = "00010001";
echo "encrypting to base sixteen: ".$gene1->encrypt();
echo "\n";echo "gene 1: ".$gene1->str_gene;
*/
?>