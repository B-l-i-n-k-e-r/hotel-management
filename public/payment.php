<?php
  require_once("./config.php");
   include("./includes/header.php");

   // Check if user is already logged in
    if (!isLoggedIn()) {
        header("Location: login.php");
    }

    if (!isset($_SESSION["reservation"])) {
        header("Location: reservation.php");
    }

?>
  <body>
    <?php include("./includes/navbar.php"); ?>
    <div class="container mt-5">
            <div class="row justify-content-between">
               <div class="col-md-5">
               <h1>Make your reservation</h1>
                     <p>
                        Our hotel is self-certified to follow a series of
                        precautionary measures to make your hotel stay safe and
                        healthy.
                     </p>
               </div>
               <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <h3 class="text-xs-center">Payment Details</h3>
                            <img class="img-fluid cc-img" src="http://www.prepbootstrap.com/Content/images/shared/misc/creditcardicons.png">
                        </div>
                    </div>
                    <form id="payment_form">
                    <div class="card-body">
                            <div class="flex-row">
                                <div class="col-xs-12 ">
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <div>
                                            <h2><b>Ksh <?= getRoomAmountFromId($_SESSION["reservation"]["room_id"], $pdo); ?></b></h2>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-row">
                                <div class="col-xs-12 ">
                                    <div class="form-group">
                                        <label>CARD NUMBER</label>
                                        <div class="input-group">
                                            <input type="tel" name="card_number" class="form-control" placeholder="Valid Card Number" required />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-xs-7 col-md-7">
                                    <div class="form-group">
                                        <label><span class="hidden-xs">EXPIRATION</span><span class="visible-xs-inline">EXP</span> DATE</label>
                                        <input type="tel" name="expiry_date" class="form-control" placeholder="MM / YY" required />
                                    </div>
                                </div>
                                <div class="col-xs-5 col-md-5 float-xs-right">
                                    <div class="form-group">
                                        <label>CV CODE</label>
                                        <input type="tel" name="cvc" class="form-control" placeholder="CVC" required />
                                    </div>
                                </div>
                            </div>
                            <div class="flex-row">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <label>CARD OWNER</label>
                                        <input type="text" name="card_owner" class="form-control" placeholder="Card Owner Names" required />
                                    </div>  
                                </div>
                            </div>
                    </div>
                    <div class="card-footer">
                        <div class="flex-row">
                            <div class="col-xs-12">
                                <button type="submit" class="btn btn-warning btn-lg btn-block">Process payment</button>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
               </div>
            </div>
         </div>
    
<?php include("./includes/footer.php"); ?>
<script>
      $(document).ready(function() {
        $("nav").eq(0).addClass("bg-dark");
        $("nav").eq(0).addClass("navbar-dark");

        $("footer").eq(0).addClass("bg-dark");
        $("footer").eq(0).addClass("text-light");


        $("#payment_form").submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        formData.append("confirm_booking", "confirm_booking");
        

        $.ajax({
            url: "core/reservation_controller.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function (data) {
            console.log("HERE");
            console.log(data); 
            if (data.error == 1) {
                // Handle payment processing error feedback here
            } else {
                let reservation_id = data.message.reservation_id;
                window.location.href = "bill_view.php?reservation_id=" + reservation_id;
            }
            },
            error: function (data, message, errorThrown) {
            console.log(errorThrown);
            },
            cache: false,
            contentType: false,
            processData: false
        });
    });
    });
    </script>
  </body>
</html>