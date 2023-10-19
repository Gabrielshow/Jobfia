
//--------(check all checkboxes)---------------------------------------------------------------------------------------

	var checked = false;
	function checkAll()
	{
		var myform = document.getElementById("form2");
		
		if (checked == false) { checked = true }else{ checked = false }
		for (var i=0; i<myform.elements.length; i++) 
		{
			myform.elements[i].checked = checked;
		}
	}



//--------(slider)----------------------------------------------------------------------------------------------------
/*
$(document).ready(function(){	
	$("#slider").easySlider({
		auto: true,
		controlsShow: false,
		speed: 800,
		pause: 2500,
		continuous: true 
	});
});
*/
//--------(cycle scroll)-----------------------------------------------------------------------------------------------

/*
$(document).ready(function() {
		$('#jobs_completed').cycle({
			fx: 'scrollDown',
			speed:    1500, 
			timeout:  4000
			//fx:     'scrollDown', 
			//speedIn:  2000, 
			//speedOut: 500, 
			//easeIn:  'bounceout', 
			//easeOut: 'backin', 
			//delay:   -2000 
		});
});
*/
//--------(fb)----------------------------------------------------------------------------------------------------------

function facebook_login() {
	FB.login(function(response) {
		window.location = '../fblogin.php';
		// Log.info('FB.login callback', response);
		if (response.session) {
			// Log.info('User is logged in');
		} else {
			// Log.info('User is logged out');
		}
	}, {scope: 'email,offline_access,publish_stream,user_birthday,user_location'});
}


///---------(top)-------------------------------------------------------------------------------------------------------

 $(document).ready(function() {
	$(window).scroll(function() {
		if ($(this).scrollTop() > 100) {
			$('.scrollup').fadeIn();
		} else {
			$('.scrollup').fadeOut();
		}
		});
 
		$('.scrollup').click(function() {
			$("html, body").animate({ scrollTop: 0 }, 600);
			return false;
		});

		$('#top').click(function() {
			$("html, body").animate({ scrollTop: 0 }, 600);
			return false;
		});
});

//--------(check all checkboxes)---------------------------------------------------------------------------------------

	var checked = false;
	function checkAll()
	{
		var myform = document.getElementById("form2");
		
		if (checked == false) { checked = true }else{ checked = false }
		for (var i=0; i<myform.elements.length; i++) 
		{
			myform.elements[i].checked = checked;
		}
	}

///---------------------------------------------------------------------------------------------------------------------

function getprice(str) {
    if (str == "") {
        document.getElementById("txtHint").innerHTML = "";
        return;
    } else {
        if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET","../ajax.php?q="+str,true);
        xmlhttp.send();
    }
}