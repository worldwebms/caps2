<?php
class file_manager { 

	var $filename;
	var $text;
	var $destinationPath;
	
	function file_manager ($where_is_file) {  //Construct the file_manager
	    $this->filename = $where_is_file;
	}
	
	function write ($text) {  
	  //Open for writing only; place the file pointer
	  // at the beginning of the file and truncate the 
	  //file to zero length. If the file does not exist,
	  //attempt to create it. 
	  
	   $this->text = $text;
	   $fp = fopen($this->filename, "w");
	   fwrite ($fp,$this->text);
	   fclose($fp);
	}
	         
	function read () { 
	   //Open for reading only; place the file pointer at the beginning of the file. 
	   $fp = fopen($this->filename, "r");
	   $this->text = fread($fp, filesize("$this->filename"));
	   fclose($fp);
	   return $this->text;
	}
	         
	         
	function append ($text) { 
   	//Open for writing only; place the file pointer at the end of the file.
   	//If the file does not exist, attempt to create it. 
   	$this->text = $text;
  	$fp = fopen($this->filename, "a");
   	fwrite ($fp,$this->text);
   	fclose($fp);
	}
	
	function delete () { //delete a file
	  unlink ($this->filename);
	}
	
	function copyto ($destinationPath) { //copy the file to a new location
		$this->destinationPath = $destinationPath;
    $ok = copy($this->filename, $this->destinationPath);
    if (!$ok) {
    	return 0;
    } else {
    	return 1;
    }
	}	
                    
}
?>