<?php
class SpecialPersianSearch extends SpecialPage {
	private $DBServer = "localhost";
	private $DBUser = "mnreadonly_user";
	private $DBPass = "mnreadme";
	private $DBName = "persiancorpus";
	private $conn = NULL;
	
    function __construct() {
        parent::__construct( 'PersianSearch' );
        $this->conn = new mysqli($this->DBServer, $this->DBUser, $this->DBPass, $this->DBName);
        // check connection
        if ($this->conn->connect_error) {
        	trigger_error('Database connection failed: '  . $this->conn->connect_error, E_USER_ERROR);
        }
        if (!$this->conn->set_charset("utf8")) {
            trigger_error("Error loading character set utf8: %s\n" . $this->conn->error);
        }
    }
 
    function __destruct() {
    	#parent::__destruct( 'PersianSearch');
    	$this->conn->close();
    	
    }
    function getSentences($searchstr, $offset, $pagesize=100) {
        $rstr = "";
    	$sql = 'SELECT id, uri, text FROM sentence '.
            'WHERE text like \'%'.mysql_real_escape_string($searchstr).'%\' ORDER BY id';
        $limq = ' LIMIT '.$pagesize;
        if ($offset) {
            $limq = ' LIMIT '.mysql_real_escape_string($offset).','.$pagesize;
        }
        $cleanq = $sql . $limq . ';';
    	$rs = $this->conn->query($cleanq);
    	if ($rs === false) {
    		trigger_error('Wrong SQL: ' . $cleanq . ' Error: ' . $this->conn->error, E_USER_ERROR);
    	} else {
    		$rows_returned = $rs->num_rows;
    	}
        if ($rows_returned == $pagesize) {
            $newoffset = $offset + $pagesize;
            $rstr .= "Displaying items $offset - ".$newoffset." &nbsp;&nbsp;";
            $rstr .= '<a href="?searchstring='.urlencode($searchstr).'&offset='.$newoffset.'">Show next '.$pagesize.'</a><br/>';
        }
    	$rs->data_seek(0);
    	$rstr .= "<table class=\"wikitable\">\n";
    	$rstr .= '<tr>';
    	$rstr .= '<th>id</th>';
    	$rstr .= '<th>ref</th>';
    	$rstr .= '<th>text</th>';
    	$rstr .= "<tr>\n";
        $mchunks = array();
        if (strpos($searchstr, '%') !== false) { 
            $mchunks = explode('%',$searchstr);
        }
    	while($row = $rs->fetch_assoc()){
    		$rstr .= '<tr>';
    		$rstr .= '<td dir="ltr">' . $row['id'] . '</td>';
    		$rstr .= '<td dir="ltr">' . $row['uri'] . '</td>';
            if ($mchunks) { 
                $senttext = $row['text'];
                foreach ($mchunks as $mtext) {
                    $mtext = trim($mtext);
                    if ($mtext != '') {
                        $senttext = str_replace($mtext, '<span style="background-color:#FFFF00;font-weight:bold">'.$mtext.'</span>', $senttext);
                    }
                }
            } else {
                $senttext = str_replace($searchstr, '<span style="background-color:#FFFF00;font-weight:bold">'.$searchstr.'</span>', $row['text']);
            }
    		$rstr .= '<td dir="rtl" lang="fa">'.$senttext.'</td>';
    		$rstr .= "</tr>\n";
    	}
    	$rstr .= "</table>\n";
    	$rs->free();
        if ($rows_returned >= $pagesize) {
            $rstr .= "<br/>\n";
            $newoffset = $offset + $pagesize;
            $rstr .= '<a href="?searchstring='.urlencode($searchstr).'&offset='.$newoffset.'">Show next '.$pagesize.'</a><br/>';
        }
        if ($rows_returned == 0) {
            $rstr .= 'Query "'.$cleanq.'" returned no results';
        }
    	return $rstr;
    }
    
    function execute( $par ) {
        $request = $this->getRequest();
        $output = $this->getOutput();
        $this->setHeaders();
 
        # Get request data from, e.g.
        $searchstr = $request->getText( 'searchstring' );
        $offset = $request->getText( 'offset' );

        $output->addHTML('<form action="" method="post">');
        $output->addHTML('Search string: <input type="text" name="searchstring" dir="rtl" value="'.mysql_real_escape_string($searchstr).'"/><input type="submit" value="Search"/><br/>');
        $output->addHTML('</form>');

        # par will contain the ID number of an LM?
 
        # Do stuff
        if (!$offset) {
            $offset = 0;
        }
        if ($searchstr) {
            $output->addWikiText( "==Search Results==" );
            $output->addHTML($this->getSentences($searchstr, $offset));
        }
    }
}
