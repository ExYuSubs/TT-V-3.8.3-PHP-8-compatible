$(document).ready(function(){
   
    $('.btn-register').click(function(){
        
          setTimeout(function(){
            $('#login-form').removeClass('fadeIn');
            $('.register').removeClass(' zoomOut');
            $('#login-form').css('animation-delay','0');
            $('.register').css(' animation-delay','0');
            $   
        },10)
        
        $('#login-form').addClass('animated zoomOut');
        $('#login-form').css('display','none');
        $('.register').addClass('animated fadeIn');
        $('.register').css('display','block');
        $('.login-container').css('height','95vh');
    })  
    
    $('.btn-login').click(function(){
        setTimeout(function(){
            $('.register').removeClass('fadeIn');
            $('#login-form').removeClass(' zoomOut');
        },10)
        
        
        $('.register').addClass('animated zoomOut');
        $('.register').css('display','none');
        $('#login-form').addClass('animated fadeIn');
        $('#login-form').css('display','block');
        $('.login-container').css('height','70vh');
    }) 
    
})