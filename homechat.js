UPDATE_TIME_MS=5000;
var tid = setTimeout(refresh, UPDATE_TIME_MS);
var messageAr = [];
const MESSAGE_QUERY_MAX=25;

var userObj;

var addChannelHTML = `
<div class="textRow addButton">
<span class="channelName" id="addChannel">
+
</span>
</div>
`;

var addChannelModal = `
<div class="inner">
    <h1>Create Channel</h1>
    <form>
        <p>Channel Name:
            <br>
            <input type="text" id="chanName" class="oneline">
        </p>
        <p>Public Access:
            <br>
            <label class="checkbox">Read:
                <input type="checkbox" id="pubR" checked>
            </label>
            <label class="checkbox">Write:
                <input type="checkbox" id="pubW" checked>
            </label>
            <label class="checkbox">Execute:
                <input type="checkbox" id="pubX" checked>
            </label>
        </p>
        <p>Group Access:
            <br>
            <label class="checkbox"><span>Read: </span>
                <input type="checkbox" id="groupR" checked>
            </label>
            <label class="checkbox">Write:
                <input type="checkbox" id="groupW" checked>
            </label>
            <label class="checkbox">Execute:
                <input type="checkbox" id="groupX" checked>
            </label>
        </p>
        <p>Private Access:
            <br>
            <label class="checkbox">Read:
                <input type="checkbox" id="privR" checked>
            </label>
            <label class="checkbox">Write:
                <input type="checkbox" id="privW" checked>
            </label>
            <label class="checkbox">Execute:
                <input type="checkbox" id="privX" checked>
            </label>
        </p>
        <br>
        <div class="slidecontainer">
            <label for="slider_sent" id="label_sent">Minimum Sentiment (Negative-Positive):
                <br>
                <input type="range" min="-1" max="1" value="-1" class="slider" step="0.01" id="slider_sent">
            </label>
        </div>
        <input type="button" id="addChaSubmit" value="Submit">
    </form>
</div>
`

var addRoleModal = `
<div class="inner">
    <h1>Create Role</h1>
    <form>
        <p>Role Name:
            <br>
            <input type="text" class="oneline" id="roleNameTxt" style="width:20vmin;">
        </p>
        <p>Role Privilege:
            <br>
            <input type="text" class="oneline" id="rolePrivTxt" style="width:5vmin;">
        </p>
        <p>
            Make Roles:
            <br>
            <br>
            <label class="checkbox">
                <input type="checkbox" id="mkRoles">
            </label>
        </p>
        <input type="button" value="Create" id = "mkRoleBtn" >
    </form>
</div>`

var editUserModal = `
<div class="inner">
	<h1>Edit User</h1>
	<form>
		<p>User Role:<br>
		<input type="text" id="roleName" class="oneline">
		</p>
		<input type="button" id="editUserSubmit" value="Submit">
	</form>
</div>
`


function removeModal() {
    let modal = $(".modal").last();
    modal.remove();
}

function makeModal(code) {
    $("html").append("<div class=\"modal\"></div>");
    let modal = $(".modal").last();
    modal.click(removeModal);
    modal.append(code);
    $( "div.modal .inner" ).click( e => {
        e.stopPropagation();
    });
}

function refresh() {
    let messages = $("#messages");
    let atBottom = ((messages.prop("scrollHeight")-messages.height()-messages.scrollTop())<32);
    fetchMessages();
    tid = setTimeout(refresh, UPDATE_TIME_MS);
    if (atBottom) {
        messages.animate({ scrollTop: messages.prop("scrollHeight")}, 1000);
    }
}

function refreshTimer() {
    clearTimeout(tid);
    tid = setTimeout(refresh, UPDATE_TIME_MS);
}

function channelPrompt() {
    makeModal(addChannelModal);
    $("#addChaSubmit").click( e=> {
        e.stopPropagation();
        let channelName=$("#chanName").val();
        //init vars
        let permPub = 0;
        let permGrp = 0
        let permPrv = 0;
        let numVal = 0;
        let sent=-1;
        //calculate octets
        let modalChildren = $("div.modal .inner").children();
        if (modalChildren.find("#privR").is(":checked")) {
            permPrv+=4;
        }
        if (modalChildren.find("#privW").is(":checked")) {
            permPrv+=2;
        }
        if (modalChildren.find("#privX").is(":checked")) {
            permPrv+=1;
        }
        if (modalChildren.find("#groupR").is(":checked")) {
            permGrp+=4;
        }
        if (modalChildren.find("#groupW").is(":checked")) {
            permGrp+=2;
        }
        if (modalChildren.find("#groupX").is(":checked")) {
            permGrp+=1;
        }
        if (modalChildren.find("#pubR").is(":checked")) {
            permPub+=4;
        }
        if (modalChildren.find("#pubW").is(":checked")) {
            permPub+=2;
        }
        if (modalChildren.find("#pubX").is(":checked")) {
            permPub+=1;
        }
        //convert to octal
        numVal = permPrv*8*8+permGrp*8+permPub;
        //sentiment
        sent=modalChildren.find("#slider_sent").val();
        $.post( "add_channel.php", { "channel_name": channelName, "octal": numVal, "sentiment":sent } ).done(function( data ) {
            fetchChannels();
            if (!data.success) {
                alert(data.error);
            }
        });
        removeModal();
    });
}

function roleAddPrompt() {
    makeModal(addRoleModal);
    $("#mkRoleBtn").click( e=> {
        e.stopPropagation();
        let roleName=$("#roleNameTxt").val();
        let rolePriv=$("#rolePrivTxt").val();
        let allowMakeMoreRoles = $("#mkRoles").is(":checked");
        let rolePrivNum = parseInt(rolePriv);
        let perms = { "role_add": allowMakeMoreRoles };
        /*
        console.log("Role Name: " + roleName);
        console.log("Role Priv Num: " + rolePrivNum);
        console.log("Role Recursion: " + allowMakeMoreRoles);
        */
        $.post( "add_role.php", { "role_name": roleName, "privilege": rolePrivNum, "permission_json":JSON.stringify(perms) } ).done(function( data ) {
            if (!data.success) {
                alert(data.error);
                console.log(data.error);
            } else {
                alert("Made Role");
            }
        });
        removeModal();
    });
}

function channelAddClick() {
    $(".channelBar .textRow").click(function() {
        $("#messages").attr("channel_id",$(this).attr("channel_id"));
        $(".channelBar .textRow").removeClass("active");
        $(this).addClass("active");
        fetchMessages(true);
    });
    $(".channelBar .textRow").last().unbind();
    $("#addChannel").click( channelPrompt );
}

function formMessage(m) {
    let time = "";
    try {
        time = m.dateCreated.split(" ")[1];
    } catch(e) {
        time="Invalid Time";
    }
    let html = `
    <div class="textRow" data-authorid="${m.owner_id}" data-privilege="${m.privilege || 0}" data-msg_id="${m.msg_id}">
    <span class="author" data-id="${m.owner_id}">${m.user_name}</span><span class="datetime">${time}</span><span class="message">${m.value}</span>
    </div>`;
    return html;
}

function formChannel(c) {
    let html = `
    <div class="textRow" channel_id="${c.channel_id}" >
    <span class="channelName">${c.name}</span>
    </div>`;
    return html;
}

function addMessages(msgAr) {
    if (messageAr.length > 0 ) {
        var ids = new Set(messageAr.map(msg => msg.msg_id));
        messageAr = [...messageAr, ...msgAr.filter(msg => !ids.has(msg.msg_id))];
    } else {
        messageAr = msgAr;
    }
    messageAr.sort((a,b) => {
        let comp = 0;
        if (a.msg_id < b.msg_id) {
            comp = -1;
        } else if (a.msg_id > b.msg_id) {
            comp = 1;
        }
        return comp;
    });
}

function fixImageHeight(img) {
    img.attr("max-width",Math.max(parseInt(img.attr("max-width")) || 0,img.width()));
    let targetWidth = Math.min( img.parent().innerWidth(), parseInt(img.attr("max-width")));
    let actualWidth = img.width();
    if (actualWidth != targetWidth) {
        img.height(img.height()/actualWidth*targetWidth);
        img.width(targetWidth);
    }
}

function processImages(imgs) {
    imgs.unbind();
    imgs.Lazy();
    imgs.each( (indx,el)=> {
        let img = $(el);
        fixImageHeight(img);
    });
    imgs.click(e=> {
        let src = e.target.src;
        let fullSrc = e.target.getAttribute("fullSrc");
        if (fullSrc!=null && fullSrc!="") {
            src=fullSrc;
        }
        makeModal("<img class=\"popout\" src=\"" + src + "\"></img>")
    });
}

function makeDeletionPopup(e, el) {
    let msgID = el.attr("data-msg_id");
    if ( (userObj.privilege!=null && userObj.privilege>parseInt(el.attr("data-privilege"))) || (userObj.user_id!=null && userObj.user_id == parseInt(el.attr("data-authorid") ) ) ) {
        let popup = $(`<div class="deletePopup clickPopup" style="top: ${e.pageY}px; left: ${e.pageX - 100}px;" ><input type="button" id="deletebtn" value="Delete"></div>`);
        popup.appendTo("body");
        popup.click(eInner => {
            eInner.stopPropagation();
        });
        $("#deletebtn").click( innerE => {
            $(".clickPopup").remove();
            innerE.stopPropagation();
            $.ajax({
                type: "POST",
                url: "delete_message.php",
                data: {"message_id":msgID},
                dataType: "json",
                cache: false,
                success: function(response) {
                    if (response.success) {
                        fetchMessages();
                    } else {
                        alert(response.error);
                    }
                }
            });
        });
        e.stopPropagation();
    }
}

function populateUserPopup(popup,userData) {
    //popup.append("<p>"+JSON.stringify(userData)+"</p>");
	popup.append("<h3>"+userData.user_name+"</h3>");
    popup.append("<p>Role:"+userData.role_name+"</p>");
    console.log(userData);
	if ((userObj.privilege || 0) > (userData.privilege || 0)) {
        popup.append("<input type='button' value='Edit User' id=\"editUsrBtn\" >");
        $("#editUsrBtn").click( e=> {
            makeModal(editUserModal);
            e.stopPropagation();
            $(".clickPopup").remove();
        });
	}
}

function writeMessages(messageContainer) {
    let prepend=-1;
    let i=0;
    for (i=0; i<messageAr.length;i++) {
        m=messageAr[i];
        if (m.domObject!=null) {
            prepend = i;
            break;
        }
    };
    for (i=prepend-1; i>=0; i--) {
        m=messageAr[i];
        m.domObject = messageContainer.prepend(formMessage(m));
        m.domObject = $(formMessage(m)).prependTo(messageContainer);
    }
    messageAr.forEach((m,index,arr) => {
        if (m.domObject==null) {
            m.domObject = $(formMessage(m)).appendTo(messageContainer);
        } else {
            prepend = false;
        }
    });
    let children = messageContainer.children();
    children.removeClass("last");
    children.last().addClass("last");
    processImages(children.find("img"));

    let authors = children.find("span.author");
    authors.unbind();
    authors.click( e => { 
        $(".clickPopup").remove();
        let popup = $(`<div class="authorPopup clickPopup" style="top: ${e.pageY}px; left: ${e.pageX}px;" ></div>`);
        popup.appendTo("body");
        
        let usrID=$(e.target).parent().attr("data-authorid");
        $.post( "fetch_user_obj.php", {user_id:usrID} ).done(function( userData ) {
            populateUserPopup(popup,userData);
        });

        popup.click(eInner => {
            eInner.stopPropagation();
        });
        e.stopPropagation();
    } );

    let times = children.find("span.datetime");
    times.unbind();
    times.click( e => { 
        $(".clickPopup").remove();
        makeDeletionPopup(e, $(e.target).parent());
    } );
}

function purgeMessages(container,purgeAr) {
    messageAr.forEach(element => {
        if (purgeAr.includes(element.msg_id)) {
            if (element.domObject!=null) {
                element.domObject.remove()
            }
        }
    });
}

function fetchMessages(purge, off) {
    let messages=$("#messages");
    let chid=messages.attr("channel_id");
    let oldHTML=$("#messages").html();
    if (purge) {
        messageAr = [];
        messages.html("");
        refreshTimer();
    }
    if (chid==null || chid==-1) {
        return;
    } else {
        $.ajax({
            type: "POST",
            url: "fetch_messages.php",
            data: {channel_id: chid, offset: off},
            dataType: "json",
            cache: false,
            success: function(response) {
                addMessages(response);
                writeMessages(messages);
                if (purge) {
                    messages.scrollTop(messages.prop("scrollHeight"));
                } else {
                    $.ajax({
                        type: "POST",
                        url: "fetch_messages_del.php",
                        data: {},
                        dataType: "json",
                        cache: false,
                        success: function(response) {
                            purgeMessages(messages,response);
                        }
                    });
                }
            }
          });
    }
}

function fetchChannels() {
    $.ajax({
        type: "POST",
        url: "fetch_channels.php",
        cache: false,
        dataType: "json",
        success: function(response) {
            let chas=$( "#channels" );
            chas.html("");
            response.forEach( chan => {
                chas.append(formChannel(chan))
            });
            chas.append(addChannelHTML);
            channelAddClick();
        }
      });
}

function sendMessage() {
    let msg = $("#msgbox").val();
    $("#msgbox").val("");
    let messages=$("#messages");
    let chid=messages.attr("channel_id");
    if (chid==null || chid==-1) {
        return;
    } else {
        $.ajax({
            type: "POST",
            url: "send_message.php",
            data: {channel_id: chid, message: msg},
            cache: false,
            success: function(response) {
                if (!response.success) {
                    alert(response.error);
                    return;
                }
                clearTimeout(tid);
                messages.scrollTop(messages.prop("scrollHeight"));
                refresh();
            }
        });
    }
}

function uploadFile() {
    let msg = $("#msgbox").val();
    $("#msgbox").val("");
    let messages=$("#messages");
    let files = $("#fileUpload")[0].files[0]; 
    let chid=messages.attr("channel_id");
    if (chid==null || chid==-1) {
        return;
    } else {
        var fd = new FormData(); 
        fd.append("channel_id",chid);
        fd.append("myfile", files);
        fd.append("message", msg);
        $.ajax({
            type: "POST",
            url: "send_file.php",
            data: fd,
            processData: false,
            contentType: false,
            success: function(response) {
                if (!response.success) {
                    alert(response.error);
                    return;
                }
                console.log(response);
                clearTimeout(tid);
                messages.scrollTop(messages.prop("scrollHeight"));
                refresh();
            }
        });
    }
}

var shiftHeld=false;
$(document).on('keyup keydown', function(e){shiftHeld = e.shiftKey} );

$(document).ready(function() {
    $("#send").click(function() {
        sendMessage();
    });
    $("#msgbox").keypress(function(event){
        let keyCode = (event.keyCode ? event.keyCode : event.which);
        if(keyCode == '13' && !shiftHeld){
            sendMessage();	
            return false;
        }
    });

    fetchChannels();

    var canPullMore = true;
    $("#messages").scroll(function(e) {
        let messages = $("#messages");
        let scroll = messages.scrollTop();
        if (scroll<64 && messages.prop("scrollHeight")>32 && canPullMore && e.originalEvent) {
            canPullMore=false;
            fetchMessages(false, messageAr.length);
            messages.scrollTop(Math.max(5,scroll));
        }
    });
    $("#messages").bind('mousewheel', function(e){
        if(e.originalEvent.wheelDelta /120 > 0) {
            if (!canPullMore) {
                let messages = $("#messages");
                messages.scrollTop(Math.max(5,messages.scrollTop()));
                return false;
            }
            //console.log('upscroll');
        }
    });

    $("#messages").on('DOMSubtreeModified', function() {
        canPullMore=true;
        let messages = $("#messages");
        messages.scrollTop(Math.max(5,messages.scrollTop()));
    });

    $("#uploadBtn").click(e=>{
        $("#fileUpload").click();
    });

    $("#fileUpload").on('change', function() {
        if ($("#fileUpload").val()!="") {
            uploadFile();
            $("#fileUpload").val("");
        }
    });

    $(window).resize( e => {
        let imgs = $(".textRow p img");
        imgs.each( (indx,el)=> {
            let img = $(el);
            fixImageHeight(img);
        });
    });

    $("body").click( e=> {
        $(".clickPopup").remove();
    });

    $.ajax({
        type: "POST",
        url: "fetch_user_obj.php",
        data: {},
        dataType: "json",
        cache: false,
        success: function(response) {
            userObj=response;
            if (userObj.permission_json!=null) {
                try {
                    let userJSON = JSON.parse(userObj.permission_json);
                    if (userJSON.role_add) {
                        $("div.footer").append(`<input type="button" value="Add Role" id="roleAdd" class="footerBtn">`);
                        $("#roleAdd").click( e=> {
                            roleAddPrompt();
                        });
                    }
                } catch( e ) {
                    console.log("Invalid user JSON");
                }
            }
        }
    });
});
