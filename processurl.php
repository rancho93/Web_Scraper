<?php
	set_time_limit(60);
	error_reporting(1);
	
	//start common part of the code
	$allowed_urls = array('a2zinterviews.com','techinterviews.com','treeknox.com');
	$data = null;
	$QA_array = null;
	
	//class to hold the question and answer as a single element
	class QA
	{
		private $_question;
		private $_answer;

		public function __construct($question, $answer)
		{
			$this->_question = $question;
			$this->_answer = $answer;
		}

		public function getquestion()
		{
			return $this->_question;  
		}

		public function getanswer()
		{
			return $this->_answer;  
		}
	}

	//process url and get host name
	$url =  $_REQUEST['url'];
	$host_url="";
	try{
		if (stripos($url,"http://") !==0)
			$url = "http://".$url;
		$host = parse_url($url, PHP_URL_HOST);
		if(isset($host['host']))
				$host_url = str_ireplace("www.","",$host);
		else  throw new Exception("invalid");
	}
	catch(Exception $e){
	echo "Invalid url";
	exit();}

	//check against list of supported sites. Call function to download the webpage. Then call a custome function to process and list the data.
	if (in_array($host_url,$allowed_urls)) {
		$fun = explode('.',$host_url);
		download_urls();
		call_user_func($fun[0]);
	}
	else {echo "Non supported site"; exit();}

	//function to download any webpage
	function download_urls(){
		global $url;
		global $data;
		try{
			$ch = curl_init();
			$timeout = 45;
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
			$headers=array('Accept:text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		        'Accept-Charset:ISO-8859-1,utf-8;q=0.7,*;q=0.3',
			'Cache-Control:max-age=0',
			'Proxy-Connection:close',
			'User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.4 (KHTML, like Gecko) Chrome/22.0.1229.94 Safari/537.4');
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			
			//proxy details for curl
			/*curl_setopt($ch, CURLOPT_PROXY, '10.1.1.1');
			curl_setopt($ch, CURLOPT_PROXYPORT, '8080');
			curl_setopt($ch,CURLOPT_PROXYAUTH,CURLAUTH_BASIC);			
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, 'user:pass');*/
			
			$data= curl_exec($ch);
			curl_close($ch);
		}
		catch(Exception $e){
			echo "Error While Downloading";
			exit();}
	}
	
	//function print the Question and answer array
	function print_array(){
	global $QA_array;
		echo '<html>
				<head>
					<script src="jquery.min.js" type="text/javascript" charset="utf-8"></script>
					<script src="jquery.jeditable.js" type="text/javascript" charset="utf-8"></script>
					<script type="text/javascript" charset="utf-8">	
					$(function() {
							
					  $(".editable_textarea").editable("return.php", { 
						  indicator : "<img src=\'indicator.gif\'>",
						  type   : \'textarea\',
						  submitdata: { _method: "put" },
						  select : true,
						  submit : \'OK\',
						  cancel : \'cancel\',
						  cssclass : "editable"
					  });
					  
					  $(".click").editable("return.php", { 
						  indicator : "<img src=\'indicator.gif\'>",
						  tooltip   : "Click to edit...",
						  style  : "inherit"
					  });
					});
					</script>
			
					<style type="text/css">
					.editable input[type=submit] {
					  color: #F00;
					  font-weight: bold;
					}
					.editable input[type=button] {
					  color: #0F0;
					  font-weight: bold;
					}
					</style>
					<script>
						function parsetable(){
							var table = document.getElementById( "dataTable" );
							var tableArr = [];
							var rows = 0;
							for ( var i = 1; i < table.rows.length; i++ ) {
							
								var s = table.rows[i].cells[0].innerHTML;
								var htmlObject = document.createElement(\'div\');
								htmlObject.innerHTML = s;
								var temp = htmlObject.getElementsByTagName("center");
								var check = document.getElementById((temp[0].innerHTML)+"_C");
								
								if(check.checked){
							
								var s = table.rows[i].cells[1].innerHTML;
								var htmlObject = document.createElement(\'div\');
								htmlObject.innerHTML = s;
								var qn = htmlObject.getElementsByTagName("p");
								
								var s = table.rows[i].cells[2].innerHTML;
								var htmlObject = document.createElement(\'div\');
								htmlObject.innerHTML = s;
								var ans = htmlObject.getElementsByTagName("p");
								tableArr.push({
									question: qn[0].innerHTML,
									answer: ans[0].innerHTML
									});
								rows++;
								}
							}
							
							if (rows!=0){
							var tablearray = JSON.stringify(tableArr);
							tablearray=" {\\"QAPairs\\": "  +tablearray+  "}";
							var request =  get_XmlHttp();
							var  the_data = "data="+encodeURIComponent(tablearray);
							request.open("POST", "posttosql.php", true);
							request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
							request.send(the_data);
							
							request.onreadystatechange = function() {
							if (request.readyState == 4) 
							
								if(request.responseText.indexOf("fail") == -1){
								alert(request.responseText+"You are being redirected now..");
								window.location.replace("index.html");
								}
								else alert("There was an error in updating your data. Please try again.");
							}}
							else alert("You have not selected any questions");
							
						}
						
						function get_XmlHttp() {
						  // create the variable that will contain the instance of the XMLHttpRequest object (initially with null value)
						  var xmlHttp = null;

						  if(window.XMLHttpRequest) 		// for Forefox, IE7+, Opera, Safari, ...
							xmlHttp = new XMLHttpRequest();
						  
						  else if(window.ActiveXObject) 	// for Internet Explorer 5 or 6
							xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
						  
						  return xmlHttp;
							}
							
						function checkall(){
							var selectflag = document.getElementById("checkallid").innerHTML;
							if (selectflag == "Select All")
								{var flag=1;document.getElementById("checkallid").innerHTML="Unselect All";}
							else {var flag=0;document.getElementById("checkallid").innerHTML="Select All";}
							var allInputs = document.getElementsByTagName("input");
							for (var i = 0, max = allInputs.length; i < max; i++){
								if (allInputs[i].type === "checkbox"){
									if (flag == 1)
										allInputs[i].checked = true;
									else allInputs[i].checked = false;
								}
							}
						}
						function inverse(){
							var allInputs = document.getElementsByTagName("input");
							for (var i = 0, max = allInputs.length; i < max; i++){
								if (allInputs[i].type === "checkbox"){
									if (allInputs[i].checked == true)
										allInputs[i].checked = false;
									else allInputs[i].checked = true;
								}
							}
						}
					</script>
				</head>
				<body>
				<center><br />
				Questions scraped from the website are given below. Click the Question or Answer to edit them. Use the checkboxes on the right to select the questions to include in the database. <br /><br /><a href="javascript:void(0)" onclick="parsetable();">Parse Table</a><br /><br /><a href="javascript:void(0)" onclick="checkall();" id="checkallid">Unselect All</a>&nbsp;&nbsp;&nbsp;
				<a href="javascript:void(0)" onclick="inverse();">Inverse Selection</a><br /><br />
				<table id="dataTable" border="1" width="90%"><tr><th>SNO</th><th>Questions</th><th>Answers</th><th>Include Question?</th></tr>';
		for($i=0;$i<count($QA_array);$i++){
			echo "<tr><td><center>".($i+1)."</center></td><td><p class='editable_textarea' style='display: inline'>".$QA_array[$i]->getquestion().'</p></td>';
			echo "<td> <p class='editable_textarea' style='display: inline'>".$QA_array[$i]->getanswer().'</p></td><td><center><input id="'.($i+1).'_C"  type="checkbox" checked="true" /></center></td></tr>';
		}	
		echo "</table></center></body></html>";
		
	}
	
	//-----end of common part of the code
	
	
	
	//individual functions to scrape data. Update the global array with list of Q&A's and call the print function to display them
	
	//function to get data from techinterviews
	function techinterviews()
	{ 
		global $data;
		global $QA_array;
		$doc = new DOMDocument();
		$doc->loadHTML($data);
		$xpath = new DOMXPath($doc);
		$ols = $xpath->query('//ol');
		$ol = $ols->item(0);
		$lis = $ol->childNodes;
		$QA_array = array();
		foreach ($lis as $li) {
			$question = $li->firstChild->nodeValue ;
			$answer = str_replace($question,"",$li->nodeValue);
			if ($question !="" && $answer!="")
				array_push($QA_array, new QA($question,$answer));
		}
		print_array();
	}
	
	//function to get data from a2zinterviews
	function a2zinterviews()
	{ 
		global $data;
		global $QA_array;
		$doc = new DOMDocument();
		$doc->loadHTML($data);
		$xpath = new DOMXPath($doc);
		
		$tables = $xpath->query('//div[@class="post-excerpt"]/table');
		$QA_array = array();
	
		foreach ($tables as $table) {
			$tds = $xpath->query("./tr/td", $table);
			$question = $tds->item(0)->nodeValue;
			$answer = $tds->item(1)->nodeValue;
			if ($question !="" && $answer!="")
				array_push($QA_array, new QA($question,$answer));
		}
		print_array();
	}
	
	//function to get data from treeknox
	function treeknox()
	{ 
		global $data;
		global $QA_array;
		$doc = new DOMDocument();
		$doc->loadHTML($data);
		$xpath = new DOMXPath($doc);
		
		$divs = $xpath->query('//div[@class="hover_1"]');
		$QA_array = array();
		foreach ($divs as $div) {
			$qns = $xpath->query('./table/tr/th[@class="th_1"]', $div);
			$question = $qns->item(0)->nodeValue;
			$ans = $xpath->query('./table/tr/td[@class="ans"]', $div);
			$answer = $ans->item(0)->nodeValue;
			
			if ($question !="" && $answer!="")
				array_push($QA_array, new QA($question,$answer));
			
		}
		print_array();
	}
	
 ?>