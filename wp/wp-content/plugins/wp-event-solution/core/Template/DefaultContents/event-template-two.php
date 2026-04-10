<!-- wp:group {"className":"attendee-ticket-wrapper","style":{"border":{"width":"2px","style":"solid","color":"#e0e0e0"},"padding":{"top":"30px","right":"30px","bottom":"30px","left":"30px"},"borderRadius":"10px","backgroundColor":"#f9fafb","boxShadow":{"color":"rgba(0,0,0,0.1)","horizontal":"0px","vertical":"5px","blur":"10px","spread":"0px"},"spacing":{"blockGap":"20px"}}} -->
<div class="wp-block-group attendee-ticket-wrapper" style="border:2px solid #e0e0e0;padding:30px;border-radius:10px;box-shadow:0px 5px 10px rgba(0,0,0,0.1);text-align:center;max-width:600px;">
    <!-- Ticket Header -->
    <!-- wp:group {"className":"ticket-header"} -->
    <div class="wp-block-group ticket-header">
        <!-- Event Title -->
        <!-- wp:heading {"level":3,"style":{"typography":{"fontSize":"26px","fontWeight":"600"},"color":{"text":"#1a202c"},"spacing":{"margin":{"bottom":"10px"}}}} -->
        <h3 style="font-size:26px;font-weight:600;color:#1a202c;margin-bottom:10px;">{{event_title}}</h3>
        <!-- /wp:heading -->

        <!-- Event Date -->
        <!-- wp:paragraph {"style":{"typography":{"fontSize":"16px","fontStyle":"italic"},"color":{"text":"#4a5568"}}} -->
        <p style="font-size:16px;font-style:italic;color:#4a5568;">Date: {{event_start_date}}</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->

    <!-- Divider -->
    <!-- wp:separator {"className":"ticket-separator","style":{"color":{"background":"#e2e8f0"},"spacing":{"margin":{"top":"20px","bottom":"20px"}}}} -->
    <hr class="wp-block-separator ticket-separator" style="background-color:#e2e8f0;margin-top:20px;margin-bottom:20px;" />
    <!-- /wp:separator -->

    <!-- Ticket Details -->
    <!-- wp:group {"className":"ticket-details","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"},"style":{"spacing":{"blockGap":"20px"}}} -->
    <div class="wp-block-group ticket-details">
        <!-- Row -->
        <!-- wp:group {"className":"ticket-row","layout":{"type":"flex","justifyContent":"space-between"},"style":{"spacing":{"blockGap":"20px"}}} -->
        <div class="wp-block-group ticket-row" style="display:flex;justify-content:space-between;width:100%;">
            <!-- Placeholder -->
            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","fontWeight":"500"},"color":{"text":"#2d3748"},"spacing":{"margin":{"bottom":"0px"}}}} -->
            <p style="font-size:18px;font-weight:500;color:#2d3748;margin-bottom:0px;">Ticket ID:</p>
            <!-- /wp:paragraph -->

            <!-- Value -->
            <!-- wp:paragraph {"style":{"typography":{"fontSize":"18px","fontWeight":"400"},"color":{"text":"#4a5568"},"spacing":{"margin":{"bottom":"0px"}}}} -->
            <p style="font-size:18px;font-weight:400;color:#4a5568;margin-bottom:0px;">{{ticket_id}}</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->

        <!-- Row -->
        <div class="wp-block-group ticket-row" style="display:flex;justify-content:space-between;width:100%;">
            <p style="font-size:18px;font-weight:500;color:#2d3748;margin-bottom:0px;">Name:</p>
            <p style="font-size:18px;font-weight:400;color:#4a5568;margin-bottom:0px;">{{attendee_name}}</p>
        </div>

        <div class="wp-block-group ticket-row" style="display:flex;justify-content:space-between;width:100%;">
            <p style="font-size:18px;font-weight:500;color:#2d3748;margin-bottom:0px;">Email:</p>
            <p style="font-size:18px;font-weight:400;color:#4a5568;margin-bottom:0px;">{{attendee_email}}</p>
        </div>

        <div class="wp-block-group ticket-row" style="display:flex;justify-content:space-between;width:100%;">
            <p style="font-size:18px;font-weight:500;color:#2d3748;margin-bottom:0px;">Price:</p>
            <p style="font-size:18px;font-weight:400;color:#4a5568;margin-bottom:0px;">{{ticket_price}}</p>
        </div>

        <div class="wp-block-group ticket-row" style="display:flex;justify-content:space-between;width:100%;">
            <p style="font-size:18px;font-weight:500;color:#2d3748;margin-bottom:0px;">Type:</p>
            <p style="font-size:18px;font-weight:400;color:#4a5568;margin-bottom:0px;">{{ticket_type}}</p>
        </div>

        <div class="wp-block-group ticket-row" style="display:flex;justify-content:space-between;width:100%;">
            <p style="font-size:18px;font-weight:500;color:#2d3748;margin-bottom:0px;">Payment Status:</p>
            <p style="font-size:18px;font-weight:400;color:#4a5568;margin-bottom:0px;">{{payment_status}}</p>
        </div>
    </div>
    <!-- /wp:group -->

    <!-- QR Code Section -->
    <!-- wp:group {"className":"ticket-qr-section","layout":{"type":"flex","justifyContent":"center"},"style":{"spacing":{"margin":{"top":"30px"}}}} -->
    <div class="wp-block-group ticket-qr-section" style="margin-top:30px;">
        <!-- QR Code -->
        <!-- wp:image {"id":0,"sizeSlug":"full","className":"qr-code","style":{"width":"150px","height":"150px"},"alt":"QR Code"} -->
        <figure class="wp-block-image qr-code" style="width:150px;height:150px;"><img src="{{qr_code}}" alt="QR Code" /></figure>
        <!-- /wp:image -->

        <!-- QR Code Instructions -->
        <!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"14px"},"color":{"text":"#4a5568"}}} -->
        <p style="text-align:center;font-size:14px;color:#4a5568;">Scan this code to verify your ticket</p>
        <!-- /wp:paragraph -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->