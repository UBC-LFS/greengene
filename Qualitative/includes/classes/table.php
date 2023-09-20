<?php

/**
 * Table class
 *
 * @author Brett Taylor
 * @package PageManager
 */
class Table
{
	var $m_columns;
	var $m_rowNum;

	var $m_firstColIsTh;
	var $m_alternateColours;

	/**
	 * Constructor
	 *
	 * @param int $p_columns number of columns in table
	 * @param bool $p_alternateColours alternate row colours/style (default true)
	 * @param bool $p_firstColIsTh first column in regular rows should be header cells (default false)
	 * @param string $p_style table style class (default null)
	 */
	// function Table($p_columns, $p_alternateColours=true, $p_firstColIsTh=false, $p_style='')
	function __construct($p_columns, $p_alternateColours=true, $p_firstColIsTh=false, $p_style='')
	{
		$this->m_columns = $p_columns;
		$this->m_row_num = 0;

		$this->m_firstColIsTh = $p_firstColIsTh;
		$this->m_alternateColours = $p_alternateColours;

		if($p_style != '')
			echo("<table class=\"$p_style\">\n");
		else
			echo("<table>\n");
	}

	/**
	 * Close (end) table
	 */
	function flush()
	{
		echo("</table>\n");
	}

	/**
	 * Write headers
	 *
	 * Uses variable-length argument lists. Include at least one parameter as header title.
	 *
	 * @param string $p_header header title
	 */
	function writeHeaders()
	{
		echo('<input type="text" id="searchBar" oninput="searchRows()" placeholder="Search..."/>');
		
		echo('<tr>');
		for($i = 0; $i < func_num_args(); $i++)
			echo('<th>' . func_get_arg($i) . "</th>\n");
		echo("</tr>\n");

		// reset row count
		$this->m_rowNum = 0;
	}

	/**
	 * Write table row
	 *
	 * Wrapper for writing TR
	 *
	 * @param string $p_cell cell data
	 */
	function writeTR()
	{
		if($this->m_alternateColours == true)
		{
			if($this->m_rowNum == 0)
			{
				echo('<tr class="row1">');
				$this->m_rowNum = 1;
			}
			else
			{
				echo('<tr class="row0">');
				$this->m_rowNum = 0;
			}
		}
		else
			echo('<tr>');
	}

	/**
	 * Write row
	 *
	 * Uses variable-length argument lists. Include at least one parameter.
	 *
	 * @param string $p_cell cell data
	 */
	function writeRow()
	{
		$this->writeTR();

		if($this->m_firstColIsTh == true)
			echo('<th>' . func_get_arg(0) . "</th>\n");
		else
			echo('<td>' . func_get_arg(0) . "</td>\n");

		for($i = 1; $i < func_num_args(); $i++)
			echo('<td>' . func_get_arg($i) . "</td>\n");

		echo("</tr>\n");
	}

	/**
	 * Write a spanning row
	 *
	 * Writes a cell that spans the entire table width.
	 *
	 * @param string $p_row cell data
	 */
	function writeSpanningRow($p_row, $p_style='')
	{
		// reset row count
		$this->m_rowNum = 0;

		if($p_style != '')
			echo('<tr class="$p_style">');
		else
			$this->writeTR();

		echo("<td colspan=\"$this->m_columns\">$p_row</td></tr>\n");
	}

	/**
	 * Write divider
	 *
	 * Writes separater (divider) in table.
	 */
	function writeDivider()
	{
		echo("<tr><td colspan=\"$this->m_columns\">&nbsp;</td></tr>");

		// reset row count
		$this->m_rowNum = 0;
	}
}

?>


<script>
	function searchRows() {
		searchInput = document.getElementById("searchBar").value.toLowerCase();
		rowsZero = document.getElementsByClassName("row0");
		rowsOne = document.getElementsByClassName("row1");

		for (let i=0; i < rowsZero.length; i++) {
			if (rowsZero[i].textContent.toLowerCase().includes(searchInput)) {
				rowsZero[i].style.display = "table-row";
			}
			else {
				rowsZero[i].style.display = "none";
			}
		}

		for (let i=0; i < rowsOne.length; i++) {
			if (rowsOne[i].textContent.toLowerCase().includes(searchInput)) {
				rowsOne[i].style.display = "table-row";
			}
			else {
				rowsOne[i].style.display = "none";
			}
		}
	}
</script>