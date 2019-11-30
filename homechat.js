UPDATE_TIME_MS=5000;
var tid = setTimeout(refresh, UPDATE_TIME_MS);
var messages = [];

var addChannelHTML = `
<div class="textRow addButton">
<span class="channelName" id="addChannel">
+
</span>
</div>
`;

function refresh() {
    fetchMessages(false);
    tid = setTimeout(refresh, UPDATE_TIME_MS);
}

function channelAddClick() {
    $(".channelBar .textRow").click(function() {
        $("#messages").attr("channel_id",$(this).attr("channel_id"));
        fetchMessages(true);
    });
    $("#addChannel").click(function() {
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
    })
}

function formMessage(m) {
    let html = "<div class=\"textRow\">";
    html+="<span class=\"author\">" + m.user_name + "</span>&nbsp;<span class=\"message\">" + m.value + "</span>";
    html+="</div>";
    return html;
}

function fetchMessages(purge) {
    let msgs=$("#messages");
    let chid=msgs.attr("channel_id");
    let oldHTML=$("#messages").html();
    if (purge) {
        messages = [];
        messageOffset = 0;
        msgs.html("");
    }
    if (chid==null || chid==-1) {
        return;
    } else {
        $.ajax({
            type: "POST",
            url: "fetch_messages.php",
            data: {channel_id: chid, offset: messageOffset},
            dataType: "json",
            cache: false,
            success: function(response) {
                if (messages.length > 0 ) {
                    var ids = new Set(messages.map(msg => msg.msg_id));
                    messages = [...messages, ...response.filter(msg => !ids.has(msg.msg_id))];
                } else {
                    messages = response;
                }
                messages.sort((a,b) => {
                    let comp = 0;
                    if (a.msg_id < b.msg_id) {
                        comp = -1;
                    } else if (a.msg_id > b.msg_id) {
                        comp = 1;
                    }
                    return comp;
                });
                let prepend=-1;
                let i=0;
                for (i=0; i<messages.length;i++) {
                    m=messages[i];
                    if (m.domObject!=null) {
                        prepend = i;
                        break;
                    }
                };
                for (i=prepend-1; i>=0; i--) {
                    m=messages[i];
                    m.domObject = msgs.prepend(formMessage(m));
                }
                messages.forEach((m,index,arr) => {
                    if (m.domObject==null) {
                        m.domObject = msgs.append(formMessage(m));
                    } else {
                        prepend = false;
                    }
                });
                /*
                $( "#messages" ).html(response).ready(function(){
                msgs = $('messages'); // your parent ul element
                msgs.children().each(function(i,msgEl){msgs.prepend(msgEl)})
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
        success: function(response) {
            $( "#channels" ).html(response+addChannelHTML);
            channelAddClick();
        }
      });
}

function sendMessage() {
    messageOffset = 0;
    let msg = $("#msgbox").val();
    $("#msgbox").val("");
    let msgs=$("#messages");
    let chid=msgs.attr("channel_id");
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

    /*
    $("#messages").scroll(function() {
        if ( $("#messages").scrollTop() == 0 && $("#messages").prop("scrollHeight")
    })*/
});