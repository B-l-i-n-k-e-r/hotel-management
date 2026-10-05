$(document).ready(function () {
  $(".input-button").click(function (event) {
    event.preventDefault();
  });

  // Check in
  $("#check-in").datepicker({
    minDate: new Date(2000, 0, 1),   // ← was 0 (today); now allows 2000 onward
    // maxDate: null,                // optional: unlimited future
    numberOfMonths: [1, 2],
    onSelect: function () {
      $("#check-in-button").html($("#check-in").val());
    },
  });

  $(".form-group:eq(0)").click(function () {
    $("#check-in").datepicker("show");
  });

  $("#check-in-button").click(function () {
    $("#check-in").datepicker("show");
  });

  // Check out
  $("#check-out").datepicker({
    minDate: new Date(2000, 0, 1),   // ← was 0 (today); now allows 2000 onward
    // maxDate: null,
    numberOfMonths: [1, 2],
    onSelect: function () {
      $("#check-out-button").html($("#check-out").val());
    },
  });

  $(".form-group:eq(1)").click(function () {
    $("#check-out").datepicker("show");
  });

  $("#check-out-button").click(function () {
    $("#check-out").datepicker("show");
  });

  parallaxScroll = () => {
    let offset = window.pageYOffset;
    try {
      console.log("HELLO");
    } catch (e) {
      console.error(e);
    }
  };
});