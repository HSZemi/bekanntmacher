<?php 

define("INKSCAPE", "/usr/bin/inkscape");
define("PDFUNITE", "/usr/bin/pdfunite");
define("TEMPLATE", "workdir/template.svg");
define("WORKDIR", "workdir/");

$pubdate = '';
$runningnr = '';
$runningyear = '';
$title = '';

$error = false;

$messages = Array();



if(isset($_POST['pubdate']) && isset($_POST['runningnr']) && isset($_POST['runningyear']) && isset($_POST['title'])){
	$pubdate 	= htmlspecialchars($_POST['pubdate']);
	$runningnr 	= intval($_POST['runningnr']);
	$runningyear 	= intval($_POST['runningyear']);
	$title 		= htmlspecialchars($_POST['title']);
	
	if($pubdate === ''){ $messages[] = '<div class="alert alert-warning" role="alert"><strong>Obacht!</strong> Bitte Bekanntmachungsdatum eingeben.</div>';$error = true;}
	if($runningnr === ''){ $messages[] = '<div class="alert alert-warning" role="alert"><strong>Obacht!</strong> Bitte laufende Nummer eingeben.</div>';$error = true;}
	if($runningyear === ''){ $messages[] = '<div class="alert alert-warning" role="alert"><strong>Obacht!</strong> Bitte Jahr der laufenden Nummer eingeben.</div>';$error = true;}
	if($title === ''){ $messages[] = '<div class="alert alert-warning" role="alert"><strong>Obacht!</strong> Bitte Titel eingeben.</div>';$error = true;}
	
	if(!$error){
		// creating title page
		
		$tmpdir = WORKDIR."tmp-".rand(10000,99999)."/";
		
		if(mkdir($tmpdir)){
			$outfilesvg = $tmpdir."akut_extra_$runningyear-$runningnr-title.svg";
			$outfilepdf = $tmpdir."akut_extra_$runningyear-$runningnr-title.pdf";
			$outfilefullpdf = $tmpdir."akut_extra_$runningyear-$runningnr.pdf";
			
			$template = file_get_contents(TEMPLATE);
			
			$search = Array("VARDATE","VARNUMBER","VARTITLE");
			$replace = Array($pubdate, $runningnr."/".$runningyear, $title);
			
			$filledtemplate = str_replace($search, $replace, $template);
			
			$outfilesvgsuccess = file_put_contents($outfilesvg, $filledtemplate);
			
			if($outfilesvgsuccess === false){
				$messages[] = '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Ausgabe-SVG der Titelseite konnte nicht erstellt werden.</div>';$error = true;
			} else {
			
				$inkscapecommand = INKSCAPE." --export-pdf=$outfilepdf $outfilesvg";
				
				exec($inkscapecommand);
				
				if(file_exists($outfilepdf)){
				
					if(isset($_FILES['userfile']) && $_FILES['userfile']['name'] != ''){
						if(substr($_FILES['userfile']['name'], -4) === ".pdf"){
					
							$uploadfile = $tmpdir."document.pdf";
							if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
								
								$pdfunitecommand = PDFUNITE." $outfilepdf $uploadfile $outfilefullpdf";
								
								exec($pdfunitecommand);
								
								if(file_exists($outfilefullpdf)){
									$messages[] = "<div class='alert alert-success' role='alert'><strong>Glückwunsch!</strong> Das hat geklappt. <a class='alert-link' href='$outfilefullpdf'>Dokument herunterladen</a></div>";
								} else {
									$messages[] = '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Die Zusammenführung von Deckblatt und Dokument ist fehlgeschlagen.</div>';$error = true;
								}
								
							} else {
								$messages[] = '<div class="alert alert-warning" role="alert"><strong>Obacht!</strong> Der Datei-Upload ist fehlgeschlagen. Ist die Datei zu groß?</div>';$error = true;
							}
						
						} else {
							$messages[] = '<div class="alert alert-warning" role="alert"><strong>Obacht!</strong> Bitte eine PDF-Datei hochladen.</div>';$error = true;
						}
					} else {
						$messages[] = "<div class='alert alert-success' role='alert'><strong>Glückwunsch!</strong> Das hat geklappt. <a class='alert-link' href='$outfilepdf'>Titelseite herunterladen</a></div>";
					}
				} else {
					$messages[] = '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Ausgabe-PDF der Titelseite konnte nicht erstellt werden.</div>';$error = true;
				}
			}
		
		} else { // tmpdir not created
			$messages[] = '<div class="alert alert-danger" role="alert"><strong>Fehler!</strong> Temp-Verzeichnis konnte nicht erstellt werden.</div>';$error = true;
		}
		
		
		
	}
	
	
}else{
	// no post request at all.
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
	<!-- Required meta tags always come first -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>Bekanntmacher</title>

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<style>
		#runningNumberSelector {width: 300px;}
		body {margin-bottom: 5em;margin-top: 2em;}
	</style>
</head>
<body>
	<div class="container">
	
	<h1>Der Bekanntmacher</h1>
	<div class="row">
<?php 

foreach($messages as $message){
	echo $message."\n";
}

?>
	</div>
	
	
	<hr>
	<form enctype="multipart/form-data" action="./index.php" method="POST">
	<div class="row">
		<div class="col-sm-3"><img src="img/pos1.png" /></div>
		<div class="col-sm-9">
		<fieldset class="form-group">
			<label for="inputPubdate">Zu welchem Datum wird die Bekanntmachung veröffentlicht?</label>
			<input type="text" class="form-control" id="inputPubdate" name="pubdate" placeholder="01. Januar 1970" value="<?php echo $pubdate; ?>">
			<small class="text-muted">Bitte in folgendem Format: 01. Januar 1970 (führende Null, Monat ausgeschrieben, Jahr vierstellig).</small>
		</fieldset>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-3"><img src="img/pos2.png" /></div>
		<div class="col-sm-9">
		<fieldset class="form-group">
			<label for="inputRunnignNr">Welche laufende Nummer hat die Bekanntmachung?</label>
			<div class="input-group" id="runningNumberSelector">
				<span class="input-group-addon" id="runningAddon">Nr.</span>
				<input type="text" class="form-control" id="inputRunnignNr" name="runningnr" placeholder="1" value="<?php echo $runningnr; ?>">
				<span class="input-group-addon" id="runningAddon">/</span>
				<input type="text" class="form-control" id="inputRunnignYear" name="runningyear" placeholder="1970" value="<?php echo $runningyear; ?>">
			</div>
			
			<small class="text-muted">Bitte in folgendem Format: Nr. 1/1970 (keine führende Null, Jahr vierstellig).</small>
		</fieldset>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-3"><img src="img/pos3.png" /></div>
		<div class="col-sm-9">
		<fieldset class="form-group">
			<label for="inputTitle">Welchen Titel hat die Bekanntmachung?</label>
			<input type="text" class="form-control" id="inputTitle" name="title" placeholder="Satzung der Fachschaft Raketenwissenschaft der Rheinischen Friedrich-Wilhelms-Universität Bonn" value="<?php echo $title; ?>">
			<small class="text-muted">Bitte den vollständigen Titel aus dem Dokument übernehmen.</small>
		</fieldset>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-3">PDF-Datei hochladen</div>
		<div class="col-sm-9">
		<input type="hidden" name="MAX_FILE_SIZE" value="300000" />
		<input name="userfile" type="file" /><br>
		<small class="text-muted">Diese Datei wird mit dem Deckblatt versehen.</small>
		</div>
	</div>
	<hr>
	<div class="row">
		<div class="col-sm-3">Fertig?</div>
		<div class="col-sm-9">
		<button class="btn btn-primary">Absenden</button>
		</div>
	</div>
	<hr>
	</form>
	</div>
</body>
</html>