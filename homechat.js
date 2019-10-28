UPDATE_TIME_MS=5000;
var tid = setTimeout(refresh, UPDATE_TIME_MS);

var addChannelHTML = `
<div class="textRow addButton">
<span class="channelName" id="addChannel">
+
</span>
</div>
`;

function refresh() {
    fetchMessages();
    tid = setTimeout(refresh, UPDATE_TIME_MS);
}

function channelAddClick() {
    $(".channelBar .textRow").click(function() {
        $("#messages").attr("channel_id",$(this).attr("channel_id"));
        fetchMessages();
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

function fetchMessages() {
    let msgs=$("#messages");
    let chid=msgs.attr("channel_id");
    let oldHTML=$("#messages").html();
    if (chid==null || chid==-1) {
        return;
    } else {
        $.ajax({
            type: "POST",
            url: "fetch_messages.php",
            data: {channel_id: chid},
            cache: false,
            success: function(response) {
                $( "#messages" ).html(response).ready(function(){
                if (oldHTML!=$("#messages").html()) {
                    $("#messages").animate({ scrollTop: $('#messages').prop("scrollHeight")}, 1000);
                }});
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
});