<?php

$passcode = (isset($_GET['passcode'])) ? $_GET['passcode'] : '';

function is_valid_email_address() {
    return (
        isset($_GET['email']) &&
        filter_var($_GET['email'], FILTER_VALIDATE_EMAIL)
    );
}

// first, check that a valid email address has been given
if (is_valid_email_address()) {

    $email = $_GET['email'];
    
    // now, get a copy of whatever version of the vCard the requester is allowed
    $url = "http://neilcrosby.com/vcard/?vcf=1&passcode=$passcode";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $contents = curl_exec($ch);

    // close cURL resource, and free up system resources
    curl_close($ch);
    
    #$contents = file_get_contents('../vcard.vcf');

    // and save it to a temporary location (so that it gets a nice filename)
    $dir = '/tmp/'.md5(time());
    mkdir($dir);
    $tempFile = $dir.'/NeilCrosby.vcf';
    file_put_contents($tempFile, $contents);

    // send the email
    require 'geekMail-1.0.php';
 
    $geekMail = new geekMail(); 
    $geekMail->setMailType('text');
 
    $geekMail->from('hire@neilcrosby.com', 'Neil Crosby');
    $geekMail->to($email);
 
    $geekMail->subject("Neil Crosby's vCard"); 
    $geekMail->message("Thanks for downloading my vCard.\n\nIf you're using an iPhone, scroll down to the bottom of the attached card to add it to your address book.\n\nDon't forget you can always get an up to date copy from http://neilcrosby.com/vcard");
 
    $geekMail->attach($tempFile);
 

    if ($geekMail->send()) {
        unlink($tempFile);
        header("Location: http://neilcrosby.com/vcard/via-email/sent/");
    } else {
        $errors = $geekMail->getDebugger();
        print_r($errors);
    }

    unlink($tempFile);
    exit();
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
    <head>
        <title>Get Neil Crosby's vCard via Email</title>
        <meta name="viewport" content="width=320" />
    </head>
    <body>
        <form method="get" action="">
            <p>
                <label for="email">Email</label>
                <input type="text" id="email" name="email">
            </p>
            <p>
                <input type="submit">
                <input type="hidden" name="passcode" value="<?php echo $passcode; ?>">
            </p>
        </form>
    </body>
</html>