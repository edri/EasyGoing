<?php
namespace Application\Utility;

// Contains function used in multiple controllers.
class Utilities
{
	// The website's default URL.
	const WEBSITE_URL = "http://easygoing.my-chic-paradise.com/";
	// Websockets and HTTP server's address.
	const EVENTS_SERVERS_ADDRESS = "localhost";

   // Inspired by https://github.com/PHPMailer/PHPMailer.
	public function sendMail($mailAddress, $subject, $message)
   {
		$mail = new PHPMailer;
		// Headers
		$mail->From = 'noreply@easygoing.com';
		$mail->FromName = 'EasyGoing!';
		$mail->addAddress($mailAddress);
		$mail->addReplyTo('noreply@easygoing.com', 'No reply');
		$mail->CharSet = 'UTF-8';

		// Content.
		// Set email format to HTML
		$mail->isHTML(true);
		$mail->Subject = 'EasyGoing! - ' . $subject;
		$mail->Body    = $message;

		// Send the mail ans return the state as a boolean value (true => OK ; false => error).
		return $mail->send();
	}

   // Function inspired by www.thewebhelp.com.
   // Used for create the images thumbnail.
   public function createSquareImage($original_file, $original_extension, $destination_file = NULL, $square_size = 96)
   {
      // Get width and height of original image.
      $imagedata = getimagesize($original_file);
      $original_width = $imagedata[0];
      $original_height = $imagedata[1];
      $new_width = 0;
      $new_height = 0;

      if ($original_width > $original_height)
      {
         $new_height = $square_size;
         $new_width = $new_height*($original_width/$original_height);
      }
      elseif ($original_width < $original_height)
      {
         $new_width = $square_size;
         $new_height = $new_width * ($original_height / $original_width);
      }
      // if $original_height == $original_width
      else
      {
         $new_width = $square_size;
         $new_height = $square_size;
      }

      $new_width = round($new_width);
      $new_height = round($new_height);
      $original_image;

      switch ($original_extension)
      {
         case "png":
         case "PNG":
            $original_image = imagecreatefrompng($original_file);
            break;
         case "jpg":
         case "JPG":
         case "jpeg":
         case "JPEG":
            $original_image = imagecreatefromjpeg($original_file);
            break;
      }

      if (!$original_image)
      {
         throw new \Exception("formatNotSupported");
      }

      $smaller_image = imagecreatetruecolor($new_width, $new_height);
      $square_image = imagecreatetruecolor($square_size, $square_size);
      // Save original image's transparancy.
      imagealphablending($smaller_image, false);
      imagesavealpha($smaller_image, true);
      imagealphablending($original_image, true);

      imagecopyresampled($smaller_image, $original_image, 0, 0, 0, 0, $new_width, $new_height, $original_width, $original_height);

      imagealphablending($square_image, false);
      imagesavealpha($square_image, true);
      imagealphablending($smaller_image, true);

      if ($new_width > $new_height)
      {
         $difference = $new_width-$new_height;
         $half_difference =  round($difference/2);
         imagecopyresampled($square_image, $smaller_image, 0-$half_difference+1, 0, 0, 0, $square_size+$difference, $square_size, $new_width, $new_height);
      }

      if ($new_width < $new_height)
      {
         $difference = $new_height-$new_width;
         $half_difference =  round($difference/2);
         imagecopyresampled($square_image, $smaller_image, 0, 0-$half_difference+1, 0, 0, $square_size, $square_size+$difference, $new_width, $new_height);
      }

      if ($new_height == $new_width)
      {
         imagecopyresampled($square_image, $smaller_image, 0, 0, 0, 0, $square_size, $square_size, $new_width, $new_height);
      }

      // If no destination file was given then display a png.
      if (!$destination_file)
      {
         imagepng($square_image, NULL, 9);
      }

      // Save the smaller image FILE if destination file given.
      if (substr_count(strtolower($destination_file), ".jpg"))
      {
         imagejpeg($square_image, $destination_file, 100);
      }

      if (substr_count(strtolower($destination_file), ".gif"))
      {
         imagegif($square_image, $destination_file);
      }

      if (substr_count(strtolower($destination_file), ".png"))
      {
         imagepng($square_image, $destination_file, 9);
      }

      imagedestroy($original_image);
      imagedestroy($smaller_image);
      imagedestroy($square_image);
   }
}
