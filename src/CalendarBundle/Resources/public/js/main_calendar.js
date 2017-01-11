// ********************************************************************
// *                       Appel Fonction
// ********************************************************************
jQuery(document).ready(function($) {

    init_plateforme();

    // RETOUR VERS LE HAUT
    // retour_haut();

    //FONTION CASE A COCHER
    // initialisation des listbox (select)
    selecteur();

    // FONCTION BOITE MODALE
    modal();

    // FONCTION COLORPICKER
    // colorpicker();

    // FONCTION DATEPICKER
    datepicker();

});

// ********************************************************************
// *                       JS General
// ********************************************************************

function init_plateforme()
{
    // pour le menu hamburger
    $('.button-collapse').sideNav();
    $('#contact-body').hide();


    $("#contact-body").hide().show(1000).css("display", "block");
    $("#access-body").hide().show(2000).css("display" ,"block");

    $('#textarea1').trigger('autoresize');

    $(".dropdown-button").dropdown();

}

// ********************************************************************
// *                       Bouton retour vers haut
// ********************************************************************

function retour_haut() {

    $('body').prepend('<a href="#" class="back-to-top">Back to Top</a>');

    var amountScrolled = 300;

    $(window).scroll(function () {
        if ($(window).scrollTop() > amountScrolled) {
            $('a.back-to-top').fadeIn('slow');
        } else {
            $('a.back-to-top').fadeOut('slow');
        }
    });

    $('a.back-to-top, a.simple-back-to-top').click(function () {
        $('html, body').animate({
            scrollTop: 0
        }, 700);
        return false;
    });
}

// ********************************************************************
// *                    material select
// ********************************************************************


function selecteur() {

    $(document).ready(function () {
        // Materialize.updateTextFields();
        $('select').material_select();
    });

}

// ********************************************************************
// *                    CALENDAR MODAL
// ********************************************************************

function modal() {

    $(document).ready(function(){

        $('.modal').modal();

    });
}

// ********************************************************************
// *                    DATE PICKER
// ********************************************************************

function datepicker() {

    $('.datepicker').pickadate({
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15 // Creates a dropdown of 15 years to control year
    });
}