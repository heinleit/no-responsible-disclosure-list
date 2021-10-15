<?php
	include(__DIR__ . '/lib/list.php');
	$list = new NRDList();
?><!DOCTYPE html>
<html>
	<?php
		$ProjectVersionNumber = 1.0;
	?>
	<head>
		<title>
			No Responsible Disclosure List Version <?= $ProjectVersionNumber ?>
		</title>

		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Ubuntu&display=swap" rel="stylesheet">

		<link rel="stylesheet" type="text/css" href="CSS/styles.css">

		<meta charset="UTF-8">
		<meta name="description" content="List of companies which disagree to Responsible Disclosure.">
		<meta name="keywords" content="IT, Responsible, Disclosure, Security, Researcher, legal, organizations">
		<meta name="Author" content="YenLucy">
		<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
	
		<link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
		<link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
		<link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
		<link rel="manifest" href="images/site.webmanifest">
	</head>
	
	<body>
		<div class="projectWrapper">
			<div class="projectIntro">
				<h1>No Responsible Disclosure List Version <?= $ProjectVersionNumber ?></h1>
				<p class="introtext">
					In einer Liste werden hier sämtliche Organisationen und Unternehmen aufgezählt, welche sich auf eindringliche Art weigern, am Responsible Disclosure-Verfahren teilzunehmen. Diese Unternehmen antworten den Sicherheitsforscher*innen, indem sie sie bei den Strafverfolgungsbehörden anzeigen - ganz im Sinne von "Shoot the messenger". Um dem entgegen zu wirken, wurde diese Liste ins Leben gerufen, welche lediglich die verschiedenen Unternehmen und die bekannten Vorfälle dieser Art zusammenträgt.
					<br>
					<a class="add-link" href="#add-entry" rel="nofollow">Eintrag hinzufügen</a>
				</p>
			</div>

			<div class="table">
				<?php
					foreach($list->getEntries() AS $entry) {
						?>
						<div class="element">
							<div class="name"><?php echo $entry->name ?></div>
							<div class="timeframeyear"><?php echo $entry->year ?></div>
							<div class="proof">
								<?php
									echo implode(", ", array_map(function ($proof) {
										return '<a target="_blank" rel="nofollow" href="' . $proof . '">Newsartikel</a>';
									}, $entry->proofs));
								?>
							</div>
						</div>
						<?php
					}
				?>
			</div>
			<div class="table-insert" id="add-entry">
				<h2>Daten hinzufügen</h2>
				<form method="post">
					<div class="table-insert-name">
						<h3>Name der Organisation/Firma:</h3>
						<input type="text" name="organization">
					</div>
					<div class="table-insert-year">
						<h3>Jahr des Zwischenfalls:</h3>
						<input type="number" name="year" min="1990" max="<?php echo date('Y') ?>" value="<?php echo date('Y') ?>">
					</div>
					<div class="table-insert-proof">
						<h3>Link als Beweis (News oder ähnliches):</h3>
						<input type="text" name="proof">
					</div>
					<input type="submit" class="table-insert-submit">
				</form>

				<?php
					if(@$_POST["organization"] != NULL && @$_POST["year"] != NULL && @$_POST["proof"] != NULL) {
						$list->addEntry($_POST["organization"], $_POST["year"], $_POST["proof"]);
					}
				?>
			</div>

			<div class="contact">
				<h2>Änderungen an Einträgen/Verbesserungsvorschläge/Kontakt:</h2>
				<a href="mailto:contact@better-save-then-sorry.de">contact@better-save-then-sorry.de</a>
			</div>
		</div>
	</body>
</html>