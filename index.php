<?php
    include './classes/Mail.php';
    include './classes/UnitedDomains.php';

    //TODO: create session
    session_start();

    $inboxVisible = false;
    $mail = NULL; //TODO: rename mail object
    $address = NULL;
    $result = -1;
    if(isset($_POST['address']))
    {
        $address = $_POST['address'];

	    $mail = new Mail();
	    $ud = new UnitedDomains();

	    $result = $ud->createAlias($address);
	    if ($result == 1) $inboxVisible = true;
    }
    if(isset($_POST['refresh']))
    {
        $address = $_POST['refresh'];
	    $mail = new Mail();
	    $inboxVisible = true;
    }
?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>mail.alias@axi.wtf</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Play:700">
</head>
<body style="background-color:#eee; font-family:'Play',serif;">
    <div class="container">
        <div class ="col-md-6 col-md-offset-3 text-center">
            <h1>create new email-alias..</h1>
            <form method="POST" action="./">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="address" placeholder="new.address">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="submit">@ axi.wtf</button>
                    </span>
                </div>
            </form>
            <br/>
            <?php
            //TODO: wrap into "showAlert"-function
            switch($result){
                case 0:
                    echo '<div class="alert alert-danger" role="alert">Error: Something went wrong! Try again.</div>';
                    break;
                case 1:
                    echo '<div class="alert alert-success" role="alert">'.$address.'@axi.wtf successfully created!'.'</div>';
                    break;
                case 2:
                    echo '<div class="alert alert-warning alert-dismissible" role="alert">'.$address.'@axi.wtf already exist!'.'</div>';
                    break;
            }
            ?>
        </div>
        <?php
        if($inboxVisible)
        {
            $inbox =
                '<div class="col-md-8 col-md-offset-2">'.
                    '<div class="panel panel-primary">'.
                        '<div class="panel-heading">'.
                            '<form method="POST" action="./">'.
                            $address.' - Inbox'.
                            '<button class="pull-right" name="refresh" value="'.$address.'" type="submit" style="border:none; background-color:Transparent">'.
                                '<span class="fa fa-refresh"></span>'.
                            '</button>'.
                            '</form>'.
                        '</div>'.
                        $mail->getPanelBody($address).
                    '</div>'.
                '</div>';
            echo $inbox;
        }
        ?>
    </div>
</body>
</html>