<?php

class importBase
{	
	protected $fileHandler = false;
	protected $columns = false;
	protected $columnAlias = array();
	protected $requiredColumns = array();
	protected $sql = false;
	private $attemptedSetupColumns = false;
	protected $cache = array();
	protected $hasHeaderRow = true;
	protected $fileType = "CSV";
	protected $debug = false;
	
	function importBase($SQLAuth)
	{
		$this->sql = $SQLAuth;
	}
	
	function useHeaderRow($use=true)
	{
		$this->hasHeaderRow = $use;
		$this->debug('Use Header Row: '.($use ? 'true' : 'false') );
	}
	
	function debug()
	{
		if ($this->debug)
		{
			$data = func_get_args();
			foreach($data as $value)
			{
				if (is_array($value))
					echo '<pre>'.print_r($value, true).'</pre>';	
				else
					echo $value.'<br />';
			}
		}
	}
	
	function lineCount($file) 
	{
		$linecount = 0;
		$handle = fopen($file, "r");
		while(!feof($handle)){
			if (fgets($handle) !== false) {
					$linecount++;
			}
		}
		fclose($handle);
		$this->debug('Number of Lines in File: '.$linecount);
		return  $linecount;     
	}
	
	function setupColumns()
	{
		if (!$this->attemptedSetupColumns)
		{
			$this->attemptedSetupColumns = true;
			if (!$this->hasHeaderRow)
			{
				for($i=0;$i<75;$i++)
				{
					$this->columns[$i]=$i;
				}
				//recordInfo('Columns in this File '.implode(', ', $this->columns));
				$this->processColumnAlias();
			}
			else
			{
				$this->columns = $this->getArrayLine(false);
				if (is_array($this->columns))
				{
					//recordInfo('Columns in this File '.implode(', ', $this->columns));
					$this->processColumnAlias();
				}
			}
		}
		
		$this->debug('Columns in file: ', $this->columns);
	}
	
	function getColumns()
	{
		if (!$this->columns)
			$this->setupColumns();

		return array_flip($this->columns);	
	}
	
	// $alias is the name of the column in the csv file, $column is the standard column
	function addColumnAlias($column, $alias)
	{
		$this->columnAlias[$alias] = $column;
		$this->processColumnAlias();
		
		$this->debug('Added Column Alias: '.$alias.' For '.$column);
	}
	
	function processFile()
	{
		$this->debug('Processing File');
		$status = $this->validateColumns();
		if ($status['valid'])
		{
			$this->doTheWork();	
		}
		else
		{
			$this->debug('The File Failed Validation:', $status['msg']);
		}
	}
	
	function doTheWork()
	{
	
	}
		
	function validateColumns()
	{
		if (!$this->columns)
			$this->setupColumns();
			
		$valid = true;
		$msg = '';
		foreach($this->requiredColumns as $column)
		{
			if (!in_array($column, $this->columns))
			{
				$valid = false;
				$msg[] = $column;
				
				$this->debug('Missing Column: '.$column);
			}
		}
		
		if (!$valid)
		{
			$msg = 'These required columns are missing: '.implode(', ', $msg);
		}
		
		return array('valid'=>$valid, 'msg'=>$msg);
	}
	
	protected function processColumnAlias()
	{
		if (!$this->columns)
			$this->setupColumns();
			
		if (is_array($this->columns))
		{
			foreach($this->columns as $key=>$column)
			{
				$column = strtoupper($column);
				$this->columns[$key] = (!empty( $this->columnAlias[$column])) ? $this->columnAlias[$column] : $column;
			}
		}
	}
	
	protected function normalizeFile($file)
	{
		$normalizedFile = $file.'.tmp';
		
		$fh = fopen($normalizedFile, 'w');
		
		if ($this->fileHandler = fopen($file, 'r'))
		{
			
			while (!feof($this->fileHandler))
			{
				$line = fgets($this->fileHandler);
				if (strlen($line)>0)
				{
					fwrite($fh, $line);
				}

			}
			$this->debug('Successfully Normalized the File: '.$file.' The new file is: '.$normalizedFile);
		}
		
		fclose($fh);
		return $normalizedFile;
	}
	
	protected function openFile($file, $flag='r')
	{
		//$file = $this->normalizeFile($file);
		//$this->debug('File Contents: ', '<pre>'.file_get_contents($file).'</pre>');
		
		if ($this->fileHandler = fopen($file, $flag))
			$this->debug('Successfully Opened File: '.$file);	
		else
			$this->debug('Failed to Open File: '.$file);
	}
	
	protected function closeFile()
	{
		fclose($this->fileHandler);
		$this->fileHandler = false;	
		$this->debug('Closed File');
	}	
	
	// Returns the raw data each line.
	protected function getLine()
	{
		if (!$this->fileHandler) {
			$this->debug('Failed to Get Line Because the File in not open.');
			return false;
		}
		if (!feof($this->fileHandler))
		{
			$line = fgets($this->fileHandler);
			$this->debug('Got Line: '.strlen($line).' '.$line);
			return $line;	
		}
		else
		{
			$this->debug('Failed to get the line because the pointer is at the end of the file.');
			return false;
		}		
	}
	
	// Returns an Array of the data on each line.
	protected function getArrayLine($columnAsKeys=true)
	{
		if ( $line = $this->getLine() )
		{
			
			if ($this->fileType=='PIPE')
				$data = $this->pipeSplit( $line );
			elseif ($this->fileType=='CSV')
				$data = $this->quoteSplit( $line );
				
			if ($columnAsKeys)
			{
				$columns = $this->getColumns();
				foreach($columns as $key=>$index)
				{
					$data[ $key ] = $data[ $index ];
					unset($data[ $index ]); 
				}
				
			}
			//$this->debug('Converted Line to Array');
			return $data;
		}
		else
		{
			
			return false;
		}	
	}	
	
	// Replace all the | on each line with a comma to make it a true CSV.
	protected function replacePipes($line)
	{
		$line = trim($line);
		return str_replace('|', ',', $line);
	}
	
	//general purpose function used when parsing pipe delimited files.  very handy.
	//give it a line from a text file and it will give you an array.
	protected function pipeSplit($s)
	{
		$array = explode('|', $s);
		for($i=0; $i<count($array); $i++)
		{
			$array[$i] = trim(trim( $array[$i], '"' ));
		}
		return $array;		
	}

	//general purpose function used when parsing csv files.  very handy.
	//give it a line from a csv and it will give you an array.
	protected function quoteSplit($s)
	{
		if ( function_exists( 'str_getcsv' ))
		{
			return str_getcsv($s);
		}
		
		$s = str_replace('""', '&quot;', $s);
		$r = array();
		$p = 0;
		$l = strlen($s);
		while ($p < $l) {
			while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false)) $p++;
			if ($s[$p] == '"') {
				$p++;
				$q = $p;
				while( ($p < $l) && ($s[$p] != '"') ){
					if($s[$p] == '\\') {
					$p += 2;
					continue;
				}
				$p++;
			}
			$r[] = stripslashes(substr($s, $q, $p-$q));
			$p++;
			while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false))
			{
				$p++;
			}
			$p++;
			}
			else
			{
				if ($s[$p] == "'")
				{
					$p++;
					$q = $p;
					while (($p < $l) && ($s[$p] != "'")) {
						if ($s[$p] == '\\')
						{
							$p += 2;
							continue;
						}
						$p++;
					}
					$r[] = stripslashes(substr($s, $q, $p-$q));
					$p++;
					while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false))
					{
						$p++;
					}
					$p++;
				}
				else
				{
					$q = $p;
					//while (($p < $l) && (strpos(",;",$s[$p]) === false)) {
					while (($p < $l) && (strpos(",",$s[$p]) === false))
					{
						$p++;
					}
					$r[] = stripslashes(trim(substr($s, $q, $p-$q)));
					while (($p < $l) && (strpos(" \r\t\n",$s[$p]) !== false))
					{
						$p++;
					}
					$p++;
				}
			}
		}
		return $r;
	}
}
	
	
?>