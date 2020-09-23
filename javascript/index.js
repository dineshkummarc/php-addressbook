// Select All/None items
function MassSelection() {
  select_count = document.getElementsByName("selected[]").length;
  all_checked  = document.getElementById("MassCB").checked;
  
  for (i = 0; i < select_count; i++) {
    // select only visible items
    if( document.getElementsByName("selected[]")[i].parentNode.parentNode.style.display != "none") {
      document.getElementsByName("selected[]")[i].checked = all_checked;
    }
  }
}

// Send Mail to selected persons
function MailSelection() {
  var addresses = "";
  var dst_count = 0;

  select_count = document.getElementsByName("selected[]").length;
  for (i = 0; i < select_count; i++) {
    selected_i = document.getElementsByName("selected[]")[i];
    if( selected_i.checked == true) {
      if( selected_i.accept != "" && selected_i.accept != null) {
        if(dst_count > 0) {
          addresses = addresses + "<?php echo getMailerDelim(); ?>";
        }
        addresses = addresses + selected_i.accept;
        dst_count++;
      }
    }
  }

  if(dst_count == 0)
    alert("No address selected.");
  else
    location.href = "<?php echo getMailer(); ?>"+addresses;
}

function Doodle() {
  var participants = "";
  var dst_count = 0;

  select_count = document.getElementsByName("selected[]").length;
  for (i = 0; i < select_count; i++) {
    selected_i = document.getElementsByName("selected[]")[i];
    if( selected_i.checked == true) {
      participants += selected_i.id+";";
      dst_count++;
    }
  }
  alert(participants);
  
  if(dst_count == 0)
    alert("No paticipants selected.");
  else
    location.href = "./doodle.php?part="+participants;
}

function DeleteSel() {
  var participants = "";
  var dst_count = 0;

  select_count = document.getElementsByName("selected[]").length;
  for (i = 0; i < select_count; i++) {
    selected_i = document.getElementsByName("selected[]")[i];
    if( selected_i.checked == true) {
      participants += selected_i.id+";";
      dst_count++;
    }
  }

  if(dst_count == 0)
    alert("No paticipants selected.");
  else {
    if(confirm('Delete '+dst_count+' addresses?')) {
      location.href = "./delete.php?part="+participants;
    }
  }
}

function applyZebra() {
  // loop over all lines
  var maintable = document.getElementById("maintable")
  var tbody     = maintable.getElementsByTagName("tbody");
  var entries   = tbody[0].children;
  var zebraCnt  = 0;

  // Skip header(0) + selection row(length-1)
  for(i = 1; i < entries.length; i++) {
    if(entries[i].style.display != "none") {
      if((zebraCnt % 2) == 0) {
        entries[i].className = "";
      } else {
        entries[i].className = "odd";
      }
      zebraCnt++;
    }
  }
}

// Filter the items in the fields
function filterResults(field) {
  var query = field.value;
  
  // split lowercase on white spaces
  var words = query.toLowerCase().split(" ");

  // loop over all lines
  var maintable = document.getElementById("maintable")
  var tbody     = maintable.getElementsByTagName("tbody");
  var entries   = tbody[0].children;
  var foundCnt  = 0;
  
  // Skip header(0) + selection row(length-1)
  for(i = 1; i < entries.length; i++) {
    // Use all columns that don't have the css class "center"
    var content = entries[i].childNodes[0].childNodes[0].accept;
    for(var j=0;j<entries[i].childNodes.length;j++) {
      if(entries[i].childNodes[j].className == "center") continue;
      content += " "+entries[i].childNodes[j].innerHTML;
    }
                        
    // Don't be case sensitive
    content = content.toLowerCase();

    // check if all words are present                       
    var foundAll = true;
    for(j = 0; j < words.length; j++) {
      foundAll = foundAll && (content.search(words[j]) != -1);
    }
            
    // Keep selected entries
    foundAll = foundAll || entries[i].childNodes[0].childNodes[0].checked;
            
    // ^Hide entry
    if(foundAll) {
      entries[i].style.display = "";
      foundCnt++;                       
    } else {
      entries[i].style.display = "none";
    }
  }
  document.getElementById("search_count").innerHTML = foundCnt;
  
  applyZebra();
} 


last_proposal = "";

function proposeMail() {
  
  if(document.theform.email.value == last_proposal) {
  
    new_proposal = "";

    has_firstname = document.theform.firstname.value != "";
    has_middlename = document.theform.middlename.value != "";
    has_lastname  = document.theform.lastname.value  != "";
  
    if(has_firstname) {
      new_proposal = document.theform.firstname.value.toLowerCase().replace(/^\s+|\s+$/g, '');
    }
    if(has_firstname && (has_middlename || has_lastname)) {
      new_proposal += ".";
    }
    if(has_lastname) {
      new_proposal += document.theform.lastname.value.toLowerCase().replace(/^\s+|\s+$/g, '');
    }
    if(has_middlename) {
      new_proposal += document.theform.middlename.value.toLowerCase().replace(/^\s+|\s+$/g, '');
    }
    if(has_middlename && has_lastname) { // middlename cannot exsist without lastname in Dutch
      new_proposal += ".";
    }
    new_proposal += "@" + document.theform.company.value.toLowerCase().replace(/^\s+|\s+$/g, '');

    new_proposal = new_proposal.replace(/ /g, "-");
    document.theform.email.value = new_proposal;
    last_proposal = new_proposal;
    
  }
}
function ucfirst(str) {
  return str.slice(0,1).toUpperCase() + str.slice(1);
}
function ucf_arr(str_arr) {
  str_res = Array();
  for (var i = 0; i < str_arr.length; i++) {
    str_res[i] = ucfirst(str_arr[i]);
  }
  return str_res;
}

function trim(str, chars) {
  no_left = str.replace(new RegExp("^[" + chars + "]+", "g"), "");
  return no_left.replace(new RegExp("[" + chars + "]+$", "g"), "");
}

function proposeNames() {
 
  document.theform.email.value = trim(document.theform.email.value, " \t");
  who_from = document.theform.email.value.split("@", 2);

  if(who_from.length >= 2) {

    who  = who_from[0].split(/[\._]+/,2);
    if(who.length == 1)  {
      who  = who_from[0].split("_",2);
    }
    if(document.theform.firstname.value == "") {
      document.theform.firstname.value = ucf_arr(who[0].split("-")).join("-");
    }
    if(who.length > 1 && document.theform.lastname.value == "") {
      document.theform.lastname.value = ucf_arr(who[1].split("-")).join("-");
    }
  }
}