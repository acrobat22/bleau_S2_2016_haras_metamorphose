/**
 * Created by acrobat on 02/12/2016.
 */

//***************************************************//
//    Materialize js pour accordeon post index       //
//*****************************************************
$(document).ready(function(){
    $('.collapsible').collapsible();
});

//***************************************************//
//    Materialize js pour select                     //
//*****************************************************
$(document).ready(function() {
    $('select').material_select();
});

$('select').material_select('destroy');

//***************************************************//
// Gestion aside post index
//***************************************************//
$('#expand').click(function(){
    $('aside').toggle(400);
});

//***************************************************//
//    NavBar                                         //
//****************************************************
$(document).ready(function() {
    Materialize.fadeInImage('#profilepic');
    Materialize.showStaggeredList('#staggered-list');
});

//***************************************************//
//    ShowDernierPost                                //
//****************************************************
//   Div de meme taille
//****************************************************
$(document).ready(function() {
    if ($(window).width() > 739) {
        var heightDiv = $('.colCustom').css('width');
        $('.colCustom').css("height", heightDiv);
        $(window).resize(function() {
            var heightDiv = $('.colCustom').css('width');
            $('.colCustom').css("height", heightDiv);
        });
    }
    else {
        var heightDiv = $('.colCustom').css('width');
        $('.colCustom').css("height", heightDiv/2);
        $(window).resize(function() {
            var heightDiv = $('.colCustom').css('width');
            $('.colCustom').css("height", heightDiv/2);
        });
    }
});

// Taille image dans div lastPostContenu
//****************************************************
// Si image dans <p>
$(document).ready( function () {
    $('.lastPostContenu > p > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});
// Si image pas dans <p>
$(document).ready( function () {
    $('.lastPostContenu > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});

// Si image dans <p>
$(document).ready( function () {
    $('.divParentContenu > p > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});
// Si image pas dans <p>
$(document).ready( function () {
    $('.divParentContenu > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});

// Si image dans <p>
$(document).ready( function () {
    $('.divCorpsPostParent > div > p > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});
// Si image pas dans <p>
$(document).ready( function () {
    $('.divCorpsPostParent > div > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});

// Si image dans <p>
$(document).ready( function () {
    $('.divEnfantContenu > p > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});
// Si image pas dans <p>
$(document).ready( function () {
    $('.divEnfantContenu > img').each(function(){
        $(this).css('height', '25%').css('width', '25%');
    });
});
//***************************************************//
//    index categorie - Accodeon                     //
//****************************************************
$(document).ready(function() {
    //ACCORDION BUTTON ACTION (ON CLICK DO THE FOLLOWING)
    $('[id^="accordionButtonCat_"]').click(function() {
        //REMOVE THE ON CLASS FROM ALL BUTTONS
        $('[id^="accordionButtonCat_"]').removeClass('on');
        //NO MATTER WHAT WE CLOSE ALL OPEN SLIDES
        $('.accordionContent').slideUp('normal');
        //IF THE NEXT SLIDE WASN'T OPEN THEN OPEN IT
        if($(this).next().is(':hidden') == true) {
            //ADD THE ON CLASS TO THE BUTTON
            $(this).addClass('on');
            //OPEN THE SLIDE
            $(this).next().slideDown('normal');
        }
    });

    /*** REMOVE IF MOUSEOVER IS NOT REQUIRED ***/
    //ADDS THE .OVER CLASS FROM THE STYLESHEET ON MOUSEOVER
    // pour toutes les catégories
    $('[id^="accordionButtonCat_"]').mouseover(function() {
        $(this).addClass('over');

    //ON MOUSEOUT REMOVE THE OVER CLASS
    }).mouseout(function() {
        $(this).removeClass('over');
    });
    /*** END REMOVE IF MOUSEOVER IS NOT REQUIRED ***/
    /***********************************************
     CLOSES ALL S ON PAGE LOAD
     ***********************************************/
    $('.accordionContent').hide();
});


//****************************
// Modal
//****************************
$(document).ready(function(){
    $('.modal').modal();
});

//****************************
// Affichage image upload avant
// validation du form
//****************************
// Actualise image après chargement sur new et edit Album
$(document).ready(function(){
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                if ($('#imageBdD').hasClass('')) {
                    $('#blah').attr('src', e.target.result);
                    $('#imageBdD').toggleClass('hide');
                }
                else {
                    $('#blah').attr('src', e.target.result);
                    // Sur NEW masque l'image vide -- Changement de la class
                    $('.hide').toggleClass('hide responsive-img');
                    // Sur EDIT masque l'image initiale -- Changement de la class
                    $('#imageBdD').toggleClass('thumbs hide');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }


    $("#fos_user_profile_form_photo_file").change(function(){
        readURL(this);
    });

    $("#fos_user_registration_form_photo_file").change(function () {
        readURL(this);
    });
});

//***************************************************//
//    Actualités forum sur homepage                  //
//****************************************************
$(document).ready(function(){
    if ($('.divContenuHome').is(':empty')) {
        $('.sliderPost-container').toggleClass('hide');
    }
});