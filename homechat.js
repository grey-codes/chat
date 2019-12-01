UPDATE_TIME_MS=5000;
var tid = setTimeout(refresh, UPDATE_TIME_MS);
var messageAr = [];
const MESSAGE_QUERY_MAX=25;

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
        <p>Channel Name:<br>
        <input type="text" id="chanName" class="oneline">
        </p>
        <p>Public Access:<br>
            <label class="checkbox">Read: <input type="checkbox" id="pubR" checked></label>
            <label class="checkbox">Write: <input type="checkbox" id="pubW" checked></label>
            <label class="checkbox">Execute: <input type="checkbox" id="pubX" checked></label>
        </p>
        <p>Group Access:<br>
            <label class="checkbox"><span>Read: </span><input type="checkbox" id="groupR" checked></label>
            <label class="checkbox">Write: <input type="checkbox" id="groupW" checked></label>
            <label class="checkbox">Execute: <input type="checkbox" id="groupX" checked></label>
        </p>
        <p>Private Access:<br>
            <label class="checkbox">Read: <input type="checkbox" id="privR" checked></label>
            <label class="checkbox">Write: <input type="checkbox" id="privW" checked></label>
            <label class="checkbox">Execute: <input type="checkbox" id="privX" checked></label>
        </p>
        <br>
        <div class="slidecontainer">
        <label for="slider_sent" id="label_sent">Minimum Sentiment (Negative-Positive):
        <br><input type="range" min="-1" max="1" value="-1" class="slider" step="0.01" id="slider_sent">
        </label>
        </div>
        <input type="button" id="addChaSubmit" value="Submit">
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
            if (data!="") {
                alert(data);
            }
        });
        removeModal();
    });
}

function channelAddClick() {
    $(".channelBar .textRow").click(function() {
        $("#messages").attr("channel_id",$(this).attr("channel_id"));
        fetchMessages(true);
    });
    $(".channelBar .textRow").last().unbind();
    $("#addChannel").click( channelPrompt );/*function() {
        let channelName=prompt("Enter the channel name:");
        let perm = prompt("Enter the three-digit octal permission.","777");
        numVal = parseInt(perm,8);
        if (numVal && numVal>=0 && numVal<512) {
          $.post( "add_channel.php", { "channel_name": channelName, "octal": numVal } ).done(function( data ) {
            fetchChannels();
            if (data!="") {
                alert(data);
            }
          });
        }
    })*/
}

function formMessage(m) {
    let html = `
    <div class="textRow">
    <span class="author">${m.user_name}</span>&nbsp;<span class="message">${m.value}</span>
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
                }
                /*
                $( "#messages" ).html(response).ready(function(){
                messages = $('messages'); // your parent ul element
                messages.children().each(function(i,msgEl){messages.prepend(msgEl)})
                if (oldHTML!=$("#messages").html() && messageOffset==0) {
                    $("#messages").animate({ scrollTop: $('#messages').prop("scrollHeight")}, 1000);
                }});
                */
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
});