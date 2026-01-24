<?php

namespace App\Mail;

class EmailLayout
{
    /**
     * Get the email header HTML
     */
    public static function getHeader()
    {
        return '
<table width="100%" style="background: linear-gradient(to right, #003366, #336699); color: #ffffff; padding: 20px; text-align: center; font-family: Arial, sans-serif; border: none;">
<tr>
<td>
<img src="https://i.imghippo.com/files/ajv8989ujg.png" alt="EduMall Logo" style="max-width: 150px; height: auto; margin-bottom: 10px; display: block; margin-left: auto; margin-right: auto;">
<h1 style="margin: 0; font-size: 24px; font-weight: bold;">EduMall Inventory System</h1>
<p style="margin: 5px 0; font-size: 16px;">Smart Inventory for Education</p>
</td>
</tr>
</table>';
    }

    /**
     * Get the email footer HTML
     */
    public static function getFooter()
    {
        return '
<table width="100%" style="background-color: #f0f0f0; color: #333333; padding: 20px; text-align: center; font-family: Arial, sans-serif; font-size: 12px; border: none;">
<tr>
<td>
<p style="margin: 5px 0;">Follow us: <a href="#" style="color: #003366; text-decoration: none;">Facebook</a> | <a href="#" style="color: #003366; text-decoration: none;">Twitter</a> | <a href="#" style="color: #003366; text-decoration: none;">LinkedIn</a></p>
<p style="margin: 5px 0;">Contact: <a href="mailto:contact@edumallug.com" style="color: #003366; text-decoration: none;">contact@edumallug.com</a> | +256 781 978 910</p>
<p style="margin: 5px 0;"><a href="http://www.edumallug.com" style="color: #003366; text-decoration: none;">www.edumallug.com</a></p>
<p style="margin: 5px 0;"><a href="#" style="color: #003366; text-decoration: none;">Privacy Policy</a> | <a href="#" style="color: #003366; text-decoration: none;">Unsubscribe</a></p>
</td>
</tr>
</table>';
    }

    /**
     * Get the full email layout with body content
     */
    public static function getLayout($body)
    {
        return '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>EduMall System Email</title>
<style>
@media only screen and (max-width: 600px) {
table { width: 100% !important; }
img { max-width: 100% !important; }
}
</style>
</head>
<body style="margin: 0; padding: 0; background-color: #f2f4f7; font-family: Arial, sans-serif;">
' . self::getHeader() . $body . self::getFooter() . '
</body>
</html>';
    }
}
