<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="en">
  <head>
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
    <meta name="x-apple-disable-message-reformatting" />
    <title>Welcome to Edumall</title>
    <style>
      body {
        background-color: rgb(255, 255, 255);
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Oxygen-Sans', Ubuntu, Cantarell, 'Helvetica Neue', sans-serif;
        margin: 0;
        padding: 0;
      }
      .container {
        max-width: 600px;
        margin: 0 auto;
        background-color: rgb(255, 255, 255);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      }
      .header {
        background: linear-gradient(135deg, rgb(20, 184, 166) 0%, rgb(59, 130, 246) 100%);
        padding: 40px 20px;
        text-align: center;
      }
      .logo {
        max-width: 180px;
        height: auto;
      }
      .content {
        padding: 40px 30px;
        color: rgb(55, 65, 81);
        line-height: 1.6;
      }
      .greeting {
        font-size: 24px;
        font-weight: 600;
        color: rgb(17, 24, 39);
        margin-bottom: 20px;
      }
      .message {
        font-size: 16px;
        margin-bottom: 30px;
      }
      .school-details {
        background-color: rgb(249, 250, 251);
        border: 1px solid rgb(229, 231, 235);
        border-radius: 8px;
        padding: 24px;
        margin: 30px 0;
      }
      .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid rgb(243, 244, 246);
      }
      .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
      }
      .detail-label {
        font-weight: 600;
        color: rgb(75, 85, 99);
      }
      .detail-value {
        color: rgb(17, 24, 39);
      }
      .cta-button {
        display: inline-block;
        background: linear-gradient(135deg, rgb(20, 184, 166) 0%, rgb(59, 130, 246) 100%);
        color: rgb(255, 255, 255);
        text-decoration: none;
        padding: 14px 28px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 16px;
        text-align: center;
        margin: 30px 0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      }
      .footer {
        background-color: rgb(249, 250, 251);
        padding: 30px;
        text-align: center;
        border-top: 1px solid rgb(229, 231, 235);
      }
      .footer-text {
        color: rgb(107, 114, 128);
        font-size: 14px;
        margin-bottom: 10px;
      }
      .contact-info {
        color: rgb(75, 85, 99);
        font-size: 12px;
      }
      .highlight {
        color: rgb(20, 184, 166);
        font-weight: 600;
      }
    </style>
  </head>
  <body>
    <table border="0" width="100%" cellpadding="0" cellspacing="0" role="presentation" align="center">
      <tbody>
        <tr>
          <td>
            <div style="display:none;overflow:hidden;line-height:1px;opacity:0;max-height:0;max-width:0">
              Welcome to Edumall - Your School Inventory Management Solution
            </div>
            <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="max-width:600px;margin:0 auto;padding:20px">
              <tbody>
                <tr>
                  <td>
                    <div class="container">
                      <!-- Header with Logo -->
                      <div class="header">
                        <img
                          alt="Edumall"
                          src="https://i.imghippo.com/files/NDPq8392iEw.png"
                          class="logo"
                          style="display:block;outline:none;border:none;text-decoration:none;margin:0 auto"
                        />
                      </div>

                      <!-- Main Content -->
                      <div class="content">
                        <div class="greeting">
                          Welcome to Edumall! 🎉
                        </div>

                        <div class="message">
                          <p>Hello <strong>{{ $school->admin_name }}</strong>,</p>

                          <p>Thank you for trusting <span class="highlight">Edumall Solutions</span> to help you manage your school's inventory and stock!</p>

                          <p>Your school account has been successfully created in the <strong>Edumall Inventory System</strong>, and we're excited to have you on board.</p>
                        </div>

                        <!-- School Details -->
                        <div class="school-details">
                          <h3 style="margin:0 0 20px 0;color:rgb(17,24,39);font-size:18px;font-weight:600;">School Details</h3>
                          <div class="detail-row">
                            <span class="detail-label">School Name:</span>
                            <span class="detail-value">{{ $school->name }}</span>
                          </div>
                          <div class="detail-row">
                            <span class="detail-label">Centre Number:</span>
                            <span class="detail-value">  {{ $school->centre_number }}</span>
                          </div>
                          <div class="detail-row">
                            <span class="detail-label">District:</span>
                            <span class="detail-value">  {{ $school->district }}</span>
                          </div>
                          <div class="detail-row">
                            <span class="detail-label">Registration Date:</span>
                            <span class="detail-value">{{ $school->created_at->format('F j, Y') }}</span>
                          </div>
                        </div>

                        <div class="message">
                          <p>Your account is now active and you can start managing your inventory through our user-friendly system. We're here to support you every step of the way in keeping your school's resources organized and accessible.</p>

                          <p>If you have any questions or need assistance getting started, please don't hesitate to contact our support team.</p>
                        </div>

                        <!-- Call to Action -->
                        <table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="text-align:center;margin:30px 0">
                          <tbody>
                            <tr>
                              <td>
                                <a href="{{ url('/login') }}" class="cta-button" target="_blank">
                                  Access Your Dashboard
                                </a>
                              </td>
                            </tr>
                          </tbody>
                        </table>

                        <p style="margin-top:30px;text-align:center;color:rgb(107,114,128);">
                          Welcome to the Edumall family!
                        </p>
                      </div>

                      <!-- Footer -->
                      <div class="footer">
                        <p class="footer-text">
                          <strong>The Edumall Uganda Team</strong>
                        </p>
                        <p class="contact-info">
                          Email: edumallug@gmail.com | Tel: +256 781 978 910<br>
                          Your trusted education partner
                        </p>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </body>
</html>
