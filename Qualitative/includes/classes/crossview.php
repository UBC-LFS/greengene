<?php
//
//require 'table.php';
//require 'cross.php';

/**
 * CrossView class
 *
 * Used to write cross tables and export cross data
 *
 * @author Mark Jia
 * @package PageManager
 */
class CrossView
{
    var $m_cross;   	// Cross object storing progeny info

	var $m_crossNums;	// Cross numbers in Cross object

	var $m_trait1Idx;	// Position in string of trait 1 gene
	var $m_trait2Idx;	// Position in string of trait 2 gene
	var $m_trait3Idx;	// Position in string of trait 3 gene

	var $m_trait1Name;	// Name of trait 1
	var $m_trait2Name;	// Name of trait 2
	var $m_trait3Name;	// Name of trait 3

	var $m_phenoNames;	// 2D array of phenotype names, in gene order

	var $m_crossCount;	// used for hiding/showing crosses (at page level)
	var $m_firstCross;	// (as above)

	/**
	 * Constructor
	 *
	 * @param Cross $p_cross Cross information
	 */
    // function CrossView($p_cross)
	function __construct($p_cross)
    {
        $this->m_cross = $p_cross;	// Store Cross object

        // Cross numbers being displayed
        $this->m_crossNums = array_keys($p_cross->m_crosses);

		// Positions in string of gene sequences for each trait
		if(isset($p_cross->m_traitOrder[0]))
	        $this->m_trait1Idx = $p_cross->m_traitOrder[0];
		if(isset($p_cross->m_traitOrder[1]))
	        $this->m_trait2Idx = $p_cross->m_traitOrder[1];
		if(isset($p_cross->m_traitOrder[2]))
	        $this->m_trait3Idx = $p_cross->m_traitOrder[2];

		// Trait names in display order
		if(isset($p_cross->m_traitNames[$this->m_trait1Idx]))
	        $this->m_trait1Name = $p_cross->m_traitNames[$this->m_trait1Idx];
		if(isset($p_cross->m_traitNames[$this->m_trait2Idx]))
	        $this->m_trait2Name = $p_cross->m_traitNames[$this->m_trait2Idx];
		if(isset($p_cross->m_traitNames[$this->m_trait3Idx]))
	        $this->m_trait3Name = $p_cross->m_traitNames[$this->m_trait3Idx];

		// Phenotype names in gene order
        $this->m_phenoNames = $p_cross->m_phenoNames;
    }

	/**
	 * Translate char at $p_idx to represent epistatic phenotype code
	 *
	 * @param string &$p_geneSeq Complete gene sequence of a cross
	 * @param int $p_idx Index in string of first epistatic gene
	 */
    function translateEpistasis(&$p_geneSeq, $p_idx)
    {
		if (isset($p_geneSeq[$p_idx + 1]) && $p_geneSeq[$p_idx + 1] != ' ')			// Has epistasis
        {
            // Convert sequence to simplify phenotype retrieval
            // Gene at $p_idx is made to represent the phenotype

            if ($p_geneSeq[$p_idx] == '3')			// Epistatic gene 1 is recessive
        	{
				if ($p_geneSeq[$p_idx + 1] == '3')	// Epistatic gene 2 is recessive
					return '3';
        	        // $p_geneSeq{$p_idx} = '3';		// bb
				else
					return '2';
        	        // $p_geneSeq{$p_idx} = '2';		// bA
        	}
        	else									// Epistatic gene 1 is dominant
        	{
				if ($p_geneSeq[$p_idx + 1] == '3')	// Epistatic gene 2 is recessive
					return '1';
       		        // $p_geneSeq{$p_idx} = '1';		// Ab
				else
					return '0';
        	        // $p_geneSeq{$p_idx} = '0';		// AA
        	}
		}
		// TODO: not sure about this
		return $p_geneSeq[$p_idx];
    }

    /**
	 * Get a textual description of a single plant.
	 *
	 * @param string[] &$p_crossData Data about a single cross
	 * @param int $p_crossNum The cross' number
	 * @return string
	 */
	function getPlantDescription($p_gene)
	{
		//$this->translateEpistasis(&$p_gene, 2);
		echo("get PlantDescription ");
		$p_gene[2] = $this->translateEpistasis($p_gene, 2);

		return "$this->m_trait1Name: " . $this->m_phenoNames[$this->m_trait1Idx][$p_gene[$this->m_trait1Idx]] .
			" | $this->m_trait2Name: " . $this->m_phenoNames[$this->m_trait2Idx][$p_gene[$this->m_trait2Idx]] .
			" | $this->m_trait3Name: " . $this->m_phenoNames[$this->m_trait3Idx][$p_gene[$this->m_trait3Idx]];
	}

	/**
	 * Write out a table with all cross informaion
	 */
	function writeCrossTable()
	{
		$cross   = $this->m_cross;              // Cross object
		$crosses = $this->m_cross->m_crosses;   // 2D array of crosses

		$firstCross = true;	// used to control hiding of crosses after the first

		$this->m_crossCount = 0;
		if(isset($this->m_crossNums[0]))
			$this->m_firstCross = $this->m_crossNums[0];

		$userId = $_GET['_userId'];

		foreach ($this->m_crossNums as $crossNum)   // Process each cross
		{
			$curCross = $crosses[$crossNum];
			$this->m_crossCount++;

			echo("<a name=\"$crossNum\"></a>");

			if($crossNum == 1)
			{
				$table = new Table(1, true, true);
				$table->writeHeaders("<a href=\"viewprogeny.php?_userId=$userId&cross=1\">Cross 1</a>
				(initial progeny)");
				$table->flush();
			}
			else
			{
				$dateStr = date("m-d-Y G:i", $curCross["Date"]);

				$table = new Table(4, false, false);
				$table->writeHeaders("<a href=\"viewprogeny.php?_userId=$userId&cross=$crossNum\">Cross $crossNum</a>
				- $dateStr",
				$this->m_trait1Name,
				$this->m_trait2Name,
				$this->m_trait3Name);

				// write pollen
				$curCross['PollenGene'][2] = $this->translateEpistasis($curCross['PollenGene'], 2);
				// $curCross[PollenGene]{2} = $this->translateEpistasis($curCross[PollenGene], 2);
				// $this->translateEpistasis($curCross[PollenGene], 2);
				$pollenCrossNum = $curCross['PollenCrossNum'];
				$pollenPlantNum = $curCross['PollenPlantNum'];
				// $pollenCrossNum = $curCross[PollenCrossNum];
				// $pollenPlantNum = $curCross[PollenPlantNum];

				$table->writeRow("Pollen:
					<a href=\"viewprogeny.php?_userId=$userId&cross=$pollenCrossNum#$pollenCrossNum-$pollenPlantNum\">Plant $pollenPlantNum</a> of
					<a href=\"viewprogeny.php?_userId=$userId&cross=$pollenCrossNum#$pollenCrossNum\">Cross $pollenCrossNum</a>",
					$this->m_phenoNames[$this->m_trait1Idx][$curCross['PollenGene'][$this->m_trait1Idx]],
					$this->m_phenoNames[$this->m_trait2Idx][$curCross['PollenGene'][$this->m_trait2Idx]],
					$this->m_phenoNames[$this->m_trait3Idx][$curCross['PollenGene'][$this->m_trait3Idx]]);
					// $this->m_phenoNames[$this->m_trait1Idx][$curCross[PollenGene]{$this->m_trait1Idx}],
					// $this->m_phenoNames[$this->m_trait2Idx][$curCross[PollenGene]{$this->m_trait2Idx}],
					// $this->m_phenoNames[$this->m_trait3Idx][$curCross[PollenGene]{$this->m_trait3Idx}]);

				// write seed
				$curCross['SeedGene'][2] = $this->translateEpistasis($curCross['SeedGene'], 2);
				// $curCross[SeedGene]{2} = $this->translateEpistasis($curCross[SeedGene], 2);
				// $this->translateEpistasis($curCross[SeedGene], 2);
				$seedCrossNum = $curCross['SeedCrossNum'];
				$seedPlantNum = $curCross['SeedPlantNum'];
				// $seedCrossNum = $curCross[SeedCrossNum];
				// $seedPlantNum = $curCross[SeedPlantNum];

				$table->writeRow("Seed:
					<a href=\"viewprogeny.php?_userId=$userId&cross=$seedCrossNum#$seedCrossNum-$seedPlantNum\">Plant $seedPlantNum</a> of
					<a href=\"viewprogeny.php?_userId=$userId&cross=$seedCrossNum#$seedCrossNum\">Cross $seedCrossNum</a>",
					$this->m_phenoNames[$this->m_trait1Idx][$curCross['SeedGene'][$this->m_trait1Idx]],
					$this->m_phenoNames[$this->m_trait2Idx][$curCross['SeedGene'][$this->m_trait2Idx]],
					$this->m_phenoNames[$this->m_trait3Idx][$curCross['SeedGene'][$this->m_trait3Idx]]);
					// $this->m_phenoNames[$this->m_trait1Idx][$curCross[SeedGene]{$this->m_trait1Idx}],
					// $this->m_phenoNames[$this->m_trait2Idx][$curCross[SeedGene]{$this->m_trait2Idx}],
					// $this->m_phenoNames[$this->m_trait3Idx][$curCross[SeedGene]{$this->m_trait3Idx}]);
				$table->flush();
			}


			// Write pollen/seed info
			// $link = "viewprogeny.php?cross=$curCross[PollenCrossNum]#$crossNum-$curCross[PollenPlantNum]";
			// $pollen = "<a href='$link'>Pollen:</a> Plant $curCross[PollenPlantNum] of Cross $curCross[PollenCrossNum] | " .
			//	$this->getPlantDescription($curCross[PollenGene]);

			//$link = "viewprogeny.php?cross=$curCross[SeedCrossNum]#$curCross[SeedPlantNum]";
			//$seed = "<a href='$link'>Seed:</a> Plant $curCross[SeedPlantNum] of Cross $curCross[SeedCrossNum] | " .
			//	$this->getPlantDescription($curCross[SeedGene]);

			//$table->writeSpanningRow(&$pollen);
			//$table->writeSpanningRow(&$seed);

			// Write table headings

			if($firstCross == true)
				echo("<img id=\"btn$crossNum\" src=\"" . URLROOT . "/includes/images/min.jpg\" onClick=\"showhideCross($crossNum);\" class=\"minmaxButton\">");
			else
				echo("<img id=\"btn$crossNum\" src=\"" . URLROOT . "/includes/images/max.jpg\" onClick=\"showhideCross($crossNum);\" class=\"minmaxButton\">");

			if($firstCross)
			{
				echo("<div id=\"cross$crossNum\" style=\"display:inline;\">");
				$firstCross = false;
			}
			else
			{
				echo("<div id=\"cross$crossNum\" style=\"display:none;\">");
			}

			$table = new Table(5);
			$table->writeHeaders("#",
						$this->m_trait1Name,
						$this->m_trait2Name,
						$this->m_trait3Name,
						"Cross as:");

			$geneSeq = $curCross["Genes"];  // Entire gene sequence of current cross
			$length = strlen($geneSeq);

			$plantNum = 0;

			for ($i = 0; $i < $length; $i += 4) // Write each plant in the cross
			{
				// pass by reference - resolved
				// Shouldn't translate epistatsis for the ones without epistasis 
				$geneSeq[$i + 2] = $this->translateEpistasis($geneSeq, $i + 2);
				$plantNum++;

				// Print phenotypes for current plant
				// $geneSeq{2} is undefined 
				$table->writeRow("<a name=\"$crossNum-$plantNum\"></a>$plantNum",
					$this->m_phenoNames[$this->m_trait1Idx][$geneSeq[$i + $this->m_trait1Idx]],
					$this->m_phenoNames[$this->m_trait2Idx][$geneSeq[$i + $this->m_trait2Idx]],
					$this->m_phenoNames[$this->m_trait3Idx][$geneSeq[$i + $this->m_trait3Idx]],
					"<input type=\"button\" value=\"Pollen\" onClick=\"addPollenCross($crossNum, $plantNum);\">
					&nbsp;&nbsp;
					<input type=\"button\" value=\"Seed\" onClick=\"addSeedCross($crossNum, $plantNum);\">");
			}
			$table->flush();

			echo("</div>");

			echo("<br><br>");
		}
	}

    /**
	 * Write out all cross data as comma seperated values
	 */
	function writeCrossesAsCSV()
    {
        $cross   = $this->m_cross;              // Cross object
        $crosses = $this->m_cross->m_crosses;   // 2D array of crosses

        foreach ($this->m_crossNums as $crossNum)   // Process each cross
        {
            $curCross = $crosses[$crossNum];

            // Print title
	    $dateStr = date("m-d-Y G:i", $curCross['Date']);

            echo "Cross $crossNum" .
            	 ($crossNum == 1 ? " (initial progeny)" : "") .
            	 " | $dateStr\n";

		if($crossNum != 1)
		{
			// Print pollen/seed info
			echo "Pollen: Plant $curCross[PollenPlantNum] of Cross $curCross[PollenCrossNum] | " .
				$this->getPlantDescription($curCross['PollenGene']) . "\n";
				// $this->getPlantDescription($curCross[PollenGene]) . "\n";
			echo "Seed: Plant $curCross[SeedPlantNum] of Cross $curCross[SeedCrossNum] | " .
				$this->getPlantDescription($curCross['SeedGene']) . "\n";
				// $this->getPlantDescription($curCross[SeedGene]) . "\n";
		}

			// Print headings

            echo "$crossNum,$this->m_trait1Name,$this->m_trait2Name,$this->m_trait3Name\n";
            // echo "$number,$this->m_trait1Name,$this->m_trait2Name,$this->m_trait3Name\n";

            $geneSeq = $curCross["Genes"];  // Entire gene sequence of current cross
            $length = strlen($geneSeq);

            $plantNum = 1;

            for ($i = 0; $i < $length; $i += 4) // Write each plant in the cross
            {
				$geneSeq[$i + 2] = $this->translateEpistasis($geneSeq, $i + 2);
				// $this->translateEpistasis(&$geneSeq, $i + 2);

                echo $plantNum . ",";

                // Print phenotypes for current plant
                echo $this->m_phenoNames[$this->m_trait1Idx][$geneSeq[$i + $this->m_trait1Idx]] . ",";
                echo $this->m_phenoNames[$this->m_trait2Idx][$geneSeq[$i + $this->m_trait2Idx]] . ",";
                echo $this->m_phenoNames[$this->m_trait3Idx][$geneSeq[$i + $this->m_trait3Idx]] . "\n";

                $plantNum++;
            }

            echo "\n";
        }
    }
}
//
//$names = array("Height", "Color", "Scent");
//$pheno = array(array("Tall", "Tall", "Tall", "Short"),
//               array("Red", "Green", "Green", "Blue"),
//               array("Fragrent", "Foul", "Unsecented", "Poignant"));
//
//$crossObj = new Cross("120", $names, $pheno);
//
//$crossObj->addCross("1", "31/01/05", "01233210",
//					  "1", "11", "0011",
//					  "2", "22", "2222");
//$crossObj->addCross("2", "09/09/05", "33331111",
//					  "1", "10", "0000",
//					  "2", "10", "0000");
//
//
//$crossView = new CrossView($crossObj);
//
//$crossView->writeCrossTable();


?>