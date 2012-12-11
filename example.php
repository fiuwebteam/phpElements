<?php
require_once("Elements.php");
require_once("Validation.php");
require_once("validationRules/user.php");

$message = "";
// Validity functions
$validation = new Validation( $rules );

if (!empty($_POST)) {
	if ($validation->validate()) {
		$message = "Valid";
	} else if (!empty($validation->errors)) {
		$message = "There are issues in your form, please correct them.";
	}
}

$html = new HTML();
$head = new HEAD(
	array(
		new TITLE("Template"),
		new LINK(array("rel" => "stylesheet", "href" => "../../css/bootstrap.min.css", "media" => "screen" )),
		new LINK(array("rel" => "stylesheet", "href" => "../../css/bootstrap-responsive.css", "media" => "screen" ))
	)
);
$body = new BODY(
	array(
		new H1("Add User"),
		( ($message != "") ? new H2($message) : "" ),
		new FORM(
			array(
				new INPUT("username"),
				new INPUT("password", array("type" => "password")),
				new SUBMIT("Submit"),
				new INPUT("Reset", array("type" => "reset", "label" => false))
			),
			array(
				"action" => $_SERVER["PHP_SELF"],
				"method" => "POST"
			)	
		)	
	)
);

$html->addChild(array($head, $body));

echo $html->output();
?>
