// <!--
function klappe(id)
{
	var klappText = document.getElementById('k' + id);
	var klappBild = document.getElementById('pic' + id); 

	if (klappText.style.display == 'none') {
  		klappText.style.display = 'block';
	}
	else {
  		klappText.style.display = 'none';
	}
}

function klappe_news(id)
{
	var klappText = document.getElementById('k' + id);
	var klappBild = document.getElementById('pic' + id); 

	if (klappText.style.display == 'none') {
  		klappText.style.display = 'block';
  		klappBild.src = 'images/minus.gif';
	}
	else {
  		klappText.style.display = 'none';
  		klappBild.src = 'images/plus.gif';
	}
}

function klappe_torrent(id)
{
	var klappText = document.getElementById('k' + id);
	var klappBild = document.getElementById('pic' + id); 

	if (klappText.style.display == 'none') {
  		klappText.style.display = 'block';
  		klappBild.src = 'images/minus.gif';
	}
	else {
  		klappText.style.display = 'none';
  		klappBild.src = 'images/plus.gif';
	}
}

  var checked = false;
  function checkAll(form)
  {
      if (checked == false)
          checked = true;
      else
          checked = false;

      var length = document.getElementById(form).elements.length; 
      
      for ( i = 0; i < length; i++ )
      {
          document.getElementById(form).elements[i].checked = checked;
      }
  } 
  
  function toggleChecked(state)
  {
      var x = document.getElementsByTagName('input');
      
      for ( i = 0; i < x.length; i++ )
      {
          if ( x[i].type == 'checkbox' )
          {
               x[i].checked = state;
          }
      }
  }
  
  function toggleDisplay(id)
  {
      var x = document.getElementById(id);
      
      if ( x.style.display == '' ) 
           x.style.display = 'none';
      else
           x.style.display = '';
  }
  
  function toggleTemplate(x)
  {
      var y = true;
      
      if ( x.form.usetemplate.selectedIndex == 0 ) 
           y = false;
           
      x.form.subject.disabled = y;
      x.form.msg.disabled = y;
      x.form.draft.disabled = y;
      x.form.template.disabled = y;
  }
  
  function read(id)
  {
      var x = document.getElementById('msg_' + id);
      var y = document.getElementById('img_' + id);
      
      if ( x.style.display == '' )
      {
           x.style.display = 'none';
           y.src = 'images/plus.gif';
      }
      else
      {
           x.style.display = '';
           y.src = 'images/minus.gif';
      }
  }

  function SmileIT(smile,form,text)
  {
      document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
      document.forms[form].elements[text].focus();
  }

  function PopMoreSmiles(form,name) 
  {
      var link = 'backend/smilies.php?action=display&form='+form+'&text='+name;
      var newWin = window.open(link,'moresmile','height=500,width=500,resizable=no,scrollbars=yes,location=no');
      if (newWin && window.focus) { newWin.focus(); }
      else { alert('Popup je blokiran od strane browsera. Dozvolite popup-ove za ovaj sajt.'); }
      return false;
  }
 
  function PopSmiles(form,name)
  {
      var link = 'moresmiles.php?form='+form+'&text='+name;
      var newWin = window.open(link,'moresmile','height=500,width=500,resizable=no,scrollbars=yes,location=no');
      if (newWin && window.focus) { newWin.focus(); }
      else { alert('Popup je blokiran od strane browsera. Dozvolite popup-ove za ovaj sajt.'); }
      return false;
  }
  
  function PopMoreTags() 
  {
      var link = 'tags.php';
      var newWin = window.open(link,'tags','height=900,width=800,resizable=yes,scrollbars=yes,location=no');
      if (newWin && window.focus) { newWin.focus(); }
      return false;
  }
  
  function PopNFORipper() 
  {
      var link = 'nforipper.php';
      var newWin = window.open(link,'nforipper','height=900,width=800,resizable=yes,scrollbars=yes,location=no');
      if (newWin && window.focus) { newWin.focus(); }
      return false;
  }
  
  function PopNFOview() 
  {
      var link = 'nfoview.php';
      var newWin = window.open(link,'nfoview','height=900,width=800,resizable=yes,scrollbars=yes,location=no');
      if (newWin && window.focus) { newWin.focus(); }
      return false;
  }
// -->