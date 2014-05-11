<?php
class mjContactPRO
{
    use Mbase;

	public function SendMail()
    {
		if (isset($_POST['mj_submit']) && $_POST['mj_submit'] == "active") {
            $this->SimpleMail();
        }
	}

	private function SimpleMail()
    {
        $mjEnableCaptcha = get_option('mjEnableCaptcha');
        $sentToMe = true;
        if (isset($mjEnableCaptcha) && $mjEnableCaptcha == '1') {
            $validate = $this->numberCaptchaValidate();
        } else {
            $validate = $this->stringCaptchaValidate('single');
        }

		if ($validate) {

            $name = strip_tags($_REQUEST['uname']);
            $email = $_REQUEST['email'];
            $subject = strip_tags($_REQUEST['subject']);
            $url = strip_tags($_REQUEST['url']);
            $comment = strip_tags($_REQUEST['comment']);
            $to = (get_option('MJmailto')) ? get_option('MJmailto') : get_option('admin_email');
            $subject = (empty($subject))? __('Contact Us Mail', 'mj-contact-us') : $subject;
            $message = $this->render(
                'ContactUsMailTemplate.php',
                array(
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'url' => $url,
                    'comment' => $comment,

                )
            );
            $headers = $this->mailHeader($to);
            $sent = wp_mail($to, $subject, $message, $headers);

            if (isset($_REQUEST['copytome']) && $_REQUEST['copytome'] == '1') {
                $sentToMe = $this->copyToMe();
            } else {
                $sentToMe = true;
            }

            if ($sent && $sentToMe) {
                $this->setMessage(__('Mail Sent Successfully', 'mj-contact-us'), 'success');
                unset($_POST);
            } else {
                $this->setMessage(__('Error While Sending Mail. Please Try Again Later', 'mj-contact-us'), 'error');
            }
        } else {
            $this->setMessage(__('Invalid Captcha', 'mj-contact-us'), 'error');
        }
	}

    private function copyToMe()
    {
        $name = strip_tags($_REQUEST['uname']);
        $email = $_REQUEST['email'];
        $subject = strip_tags($_REQUEST['subject']);
        $url = strip_tags($_REQUEST['url']);
        $comment = strip_tags($_REQUEST['comment']);
        $to = $email;
        $subject = (empty($subject)) ? __('Contact Us Mail', 'mj-contact-us') : $subject;
        $message = self::render(
            'ContactUsMailTemplate.php',
            array(
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'url' => $url,
                'comment' => $comment,
            )
        );
        $headers = $this->mailHeader($to);
        $sent = wp_mail($to, $subject, $message, $headers);
        if ($sent) {
            return true;
        } else {
            return false;
        }
    }

	public static function SimpleMailWithAttachment()
    {
        $mjEnableCaptcha = get_option('mjEnableCaptcha');
        if (isset($mjEnableCaptcha) && $mjEnableCaptcha == '1') {
            $validate = self::numberCaptchaValidate();
        } else {
            $validate = self::stringCaptchaValidate('single');
        }

        if ($validate) {
			if ($_FILES['file']['name']!="") {
					self::copytome();
					$name		=	strip_tags($_REQUEST['uname']);
					$email		=	$_REQUEST['email'];
					$subject	=	strip_tags($_REQUEST['subject']);
					$url		=	strip_tags($_REQUEST['url']);
					$comment	=	strip_tags($_REQUEST['comment']);
					$to			=	(get_option('MJmailto')) ? get_option('MJmailto') : get_option('admin_email');
					$subject 	=	(empty($subject))? __('Contact Us Mail', 'mj-contact-us') : $subject;
					
				
					$msg = "
							<p>Hello Admin</p>
							<p>Please find the details of contact us mail send by a new user</p>
							<p>=============================================================</p>
							<table>
								<tr>
									<th>Name : </th>
									<td>{$name}</td>
								</tr>
								<tr>
									<th>Email : </th>
									<td>{$email}</td>
								</tr>
								<tr>
									<th>Subject : </th>
									<td>{$subject}</td>
								</tr>
								<tr>
									<th>Website : </th>
									<td>{$url}</td>
								</tr>
								<tr>
									<th>Comment : </th>
									<td>{$comment}</td>
								</tr>
							</table>
							<p>=============================================================</p>
							<p>Thanks & Regards</p>";
							$msg="this is testing message";
						$response = self::sendEmail($name, $email, $to, $subject, $msg, $_FILES['file']);
					if($response){
						echo "<div class='updatedcss' id='message'> Mail Sent Successfully</div>";
					}else{
						echo "<div class='Eerror p-12' id='message'> Error: Mail Not Sent. Please try again later</div>";
					}
				}else{
					echo "<div class='Eerror p-12' id='message'>Please Upload Files</div>";
				}
		    }else{
                echo "<div class='Eerror p-12' id='message'>Invalid Captcha</div>";
            }
	}
	
	 function sendEmail($name, $email, $to_mail, $subject, $msg, $attachment = "")
     {
			$sending = false;

			if (!empty($attachment['tmp_name']) && !empty($attachment['error'])) $attachment['tmp_name'] = "";

			if (!empty($name) && !empty($email) && !empty($to_mail) && !empty($subject) && !empty($msg)) {
				$from_name = $name;
				$from_mail = $email;
				$sending = true;
			}

			if ($sending) {
				$eol = "\n";
				$tosend['email'] = $to_mail;
				$tosend['subject'] = $subject;
				$tosend['message'] = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
					<html>
					<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">
					<title>\".$subject.\"</title>
					</head>
					<body> 
					".$msg."<br />
					</body>
					</html>
					".$eol.$eol;

				$tosend['headers'] = "From: \"".$from_name."\" <".$from_mail.">".$eol;
				$tosend['headers'] .= "Return-path: <".$from_mail.">".$eol;
				$tosend['headers'] .= "MIME-Version: 1.0".$eol;
				if (!empty($attachment['tmp_name'])) {
					$file = $attachment['tmp_name'];
					$content = file_get_contents($file);
					$content = chunk_split(base64_encode($content));
					$uid = md5(uniqid(time()));
					$f_name = $attachment['name'];
					$tosend['headers'] .= "Content-Type: multipart/mixed; boundary=\"PHP-mixed-".$uid."\"".$eol.$eol;
					$tosend['headers'] .= "This is a multi-part message in MIME format.".$eol;
					$tosend['headers'] .= "--PHP-mixed-".$uid."".$eol;
					$tosend['headers'] .= "Content-Type: multipart/alternative; boundary=\"PHP-alt-".$uid."\"".$eol.$eol;
					$tosend['headers'] .= "--PHP-alt-".$uid."".$eol;
					$tosend['headers'] .= "Content-type: text/html; charset=utf-8".$eol;
					$tosend['headers'] .= "Content-Transfer-Encoding: 7bit".$eol.$eol;
					$tosend['headers'] .= $tosend['message']."".$eol.$eol;
					$tosend['headers'] .= "--PHP-alt-".$uid."--".$eol;
					$tosend['headers'] .= "--PHP-mixed-".$uid."".$eol;
					$tosend['headers'] .= "Content-Type: application/octet-stream; name=\"".$f_name."\"".$eol; // use diff. types here
					$tosend['headers'] .= "Content-Transfer-Encoding: base64".$eol;
					$tosend['headers'] .= "Content-Disposition: attachment; filename=\"".$f_name."\"".$eol.$eol;
					$tosend['headers'] .= $content."".$eol.$eol;
					$tosend['headers'] .= "--PHP-mixed-".$uid."--";
					$tosend['message'] = "";//-- The message is already in the headers.
				} 
				$sent	=	mail($tosend['email'],$tosend['subject'],$tosend['message'],$tosend['headers']);
				if ($sent)
					return true;
				else
					return false;
			}
    return false;
	} 

	function AdminOptionProcess()
    {
		if (isset($_POST['action']) && $_POST['action'] == "insert") {
			$mailTo		    =	(!empty($_POST['to'])) ? $_POST['to'] : get_option('admin_email');
			$copyToMe		=	isset($_POST['copy'])? 1 :0;
			$name			=	isset($_POST['name'])? 1 :0;
			$email		    =	isset($_POST['email'])? 1 :0;
			$subject		=	isset($_POST['subject'])? 1 :0;
			$website		=	isset($_POST['website'])? 1 :0;
			$comment		=	isset($_POST['comment'])? 1 :0;
			$attachment	    =	isset($_POST['attachment'])? 1 :0;
			$captcha	    =	$_POST['captcha'];

			if(is_email($mailTo)){
				update_option('MJmailto',$mailTo);
				update_option('MJcopytome',$copyToMe);
				update_option('MJname',$name);
				update_option('MJemail',$email);
				update_option('MJsubject',$subject);
				update_option('MJwebsite',$website);
				update_option('MJcomment',$comment);
				update_option('MJattachment',$attachment);
				update_option('mjEnableCaptcha',$captcha);
                $this->setMessage('<p>'.__('Success : Data save successfully', 'mj-contact-us').'</p>', 'success');
			} else {
                $this->setMessage('<p>'.__('Error : Please enter valid email address', 'mj-contact-us').'</p>', 'error');
			}
		}
	}
	
	function AdminSwitch()
    {
		switch(ACTION){
			case 1:
				_e('action');
			break;
			default : 
				self::AdminOptionProcess();
				return mjContactHTML::AdminOption();
			break;
		}
	}

    public static function AddFormProcess()
    {
        $Response    =   MjFunctions::addForm();
        if($Response){
            switch($Response){
                case "EmptyFormName":
                    $errorCode  =   'E-FRM-NAME';
                    break;
                case "EmptyFormId":
                    $errorCode  =   'E-FRM-ID';
                    break;
                case '1':
                    $errorCode  =   'S-INSERTED';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
            }
            MjFunctions::message($errorCode);  //MjFunctions::mRedirect($errorCode,ADDFORM);
        }
    }
    
public static function EditFormProcess()
{
        $Response	=	'';
		$Response    =   MjFunctions::EditForm();
        if($Response){
            switch($Response){
                case "EmptyFormName":
                    $errorCode  =   'E-FRM-NAME';
                    break;
                case "EmptyFormId":
                    $errorCode  =   'E-FRM-ID';
                    break;
                case '1':
                    $errorCode  =   'S-INSERTED';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
			#$url    =   add_query_arg(array('id'=>$_REQUEST['id']), EDITFORM);
            MjFunctions::message($errorCode);	#MjFunctions::mRedirect($errorCode,$url);
        }
    }
	
    public static function updateFormStatusProcess(){
        $Response    =   MjFunctions::updateFormStatus();
        if($Response){
            switch($Response){
                case "InValidCode":
                    $errorCode  =   'E-FRM-INVALID';
                    break;
                case '1':
                    $errorCode  =   'STATUS';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
            MjFunctions::mRedirect($errorCode,FORMURL);
        }
    }
    public static function deleteFormProcess(){

        $Response    =   MjFunctions::deleteForm();
        if($Response){
            switch($Response){
                case "InValidCode":
                    $errorCode  =   'E-FRM-INVALID';
                    break;
                case '1':
                    $errorCode  =   'DELETED';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
            MjFunctions::mRedirect($errorCode,FORMURL);
        }
    }

    /*
     *  add field process function
     */

    public static function addFieldProcess(){
        $Response    =   MjFunctions::addField();
        if($Response){
            switch($Response){
                case "EmptyColumnName":
                    $errorCode  =   'E-FIELD-NAME';
                    break;
                case "EmptyColumnId":
                    $errorCode  =   'E-FIELD-ID';
                    break;
                case "EmptyColumnType":
                    $errorCode  =   'E-FIELD-TYPE';
                    break;
                case "EmptyFormType":
                    $errorCode  =   'E-FIELD-FORM';
                    break;
                case '1':
                    $errorCode  =   'S-INSERTED';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
            MjFunctions::mRedirect($errorCode,ADDFIELD);
        }
    }    /*
     *  add field process function
     */

    public static function editFieldProcess(){
        $Response    =   MjFunctions::editField();
        if($Response){
            switch($Response){
                case "EmptyColumnName":
                    $errorCode  =   'E-FIELD-NAME';
                    break;
                case "EmptyColumnId":
                    $errorCode  =   'E-FIELD-ID';
                    break;
                case "EmptyColumnType":
                    $errorCode  =   'E-FIELD-TYPE';
                    break;
                case "EmptyFormType":
                    $errorCode  =   'E-FIELD-FORM';
                    break;
                case '1':
                    $errorCode  =   'S-INSERTED';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
            $url    =   add_query_arg(array('id'=>$_REQUEST['id']), EDITFIELD);
            MjFunctions::mRedirect($errorCode,$url);
        }
    }
    /*
     *  update field status process function
     */
    public static function updateFieldStatusProcess(){
        $Response    =   MjFunctions::updateFieldStatus();
        if($Response){
            switch($Response){
                case "InValidCode":
                    $errorCode  =   'E-FRM-INVALID';
                    break;
                case '1':
                    $errorCode  =   'STATUS';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
            MjFunctions::mRedirect($errorCode,FIELDSURL);
        }
    }

    /*
     * delete field process function
     */

    public static function deleteFieldProcess(){

        $Response    =   MjFunctions::deleteField();
        if($Response){
            switch($Response){
                case "InValidCode":
                    $errorCode  =   'E-FRM-INVALID';
                    break;
                case '1':
                    $errorCode  =   'DELETED';
                    break;
                default:
                    $errorCode  =   'E-FRM-INVALID';
                    break;
            }
            MjFunctions::mRedirect($errorCode,FIELDSURL);
        }
    }

	function ManageFields(){
		switch(ACTION){
			case 'add':
				self::addFieldProcess();
				mjContactHTML::addFieldHtml();
			break;
			case 'status':
				self::updateFieldStatusProcess();
			break;
			case 'delete':
				self::deleteFieldProcess();
			break;
			case 'edit':
				self::editFieldProcess();
				mjContactHTML::editFieldHtml();
			break;
			default :
				mjContactHTML::getFieldList();
			break;
		}
	}
	
	function ManageStoreForms(){
		switch(ACTION){
			case 'view':
				mjContactHTML::StoreDataDetail();
			break;
			default :
				mjContactHTML::getStoreFormList();
			break;
		}
	}
	

    function ManageForms()
    {
		switch(ACTION){
			case 'add':
                mjContactHTML::AddFormProcess();
                mjContactHTML::AddFormHtml();
			break;
            case 'edit':
                self::EditFormProcess();
                mjContactHTML::EditFormHtml();
                break;
            case 'status':
                mjContactHTML::updateFormStatusProcess();
                break;
            case 'delete':
                mjContactHTML::deleteFormProcess();
                break;
			default :
				mjContactHTML::getFormList();
			break;
		}
	}

    public function stringCaptchaValidate($code	=	""){

        if ($_REQUEST['captcha'] ==  $_SESSION['captcha_'.$code]) {
            return true;
        } else {
            return false;
        }
    }

    public function numberCaptchaValidate(){
        if($_REQUEST['captcha'] ==   MjFunctions::BaseDecode($_POST['CODEINCODE'])){
            return true;
        }else{
            return false;
        }
    }

    public static function dynamicFormProcess(){
        if(isset($_POST['dnForm'])){
			//$validate	=	MjFunctions::DmFormValidation();
            $mail   	=   MjFunctions::sendDynamicMail();
            if($mail){
                echo "send";
            }else{
                echo "exit";
            }
        }
    }
	

}
?>
