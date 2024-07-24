<?php
include('database_connection.php');
session_start();

if(!isset($_SESSION['user_id']))
{
    header("location:login.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Secure Chat Hub</title>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.css">
    <style>
        body {
            background-color: lightyellow;
            transition: background-color 0.5s;
        }
        .dark-mode {
            background-color: #333;
            color: #FFF;
        }
        .container {
            margin-top: 50px;
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #000;
            color: #FFF;
            padding: 10px 20px;
            border-bottom: 2px solid black;
			text-align:center;
        }
        .header-title {
            text-align: center;
            font-size: 35px;
            color: red;
            font-weight: bold;
            text-shadow: 2px 2px 4px #000000;
            margin: 0;
			
        }
        .current-datetime {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 15px;
            margin-left: auto;
        }
        .logout {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 18px;
            padding: 10px;
            background-color: #000;
            color: #FFF;
        }
        .logout a {
            font-size: 18px;
            padding: 10px 20px;
            background-color: #ff4757;
            color: #FFF;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            margin-left: 10px;
        }
        .logout a:hover {
            background-color: #e84118;
        }
        .toggle-switch {
            position: fixed;
            bottom: 10px;
            left: 10px;
            background-color: aqua;
            border: none;
            padding: 8px;
            font-size: 14px;
            cursor: pointer;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .table-responsive {
            border: 1px solid black;
        }
    </style>
</head>
<body>
    <div class="header-container">
        <h1 class="header-title">Secure Chat Hub</h1>
        <div class="current-datetime">
            <h2 style="font-size:25px">Date & Time:</h2>
            <p id="datetime"></p>
        </div>
        <p class="logout">Hi - <?php echo $_SESSION['username']; ?> - <a href="logout.php">Logout</a></p>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-6">
                <h4>Online Users</h4>
            </div>
            <div class="col-md-2 col-sm-3">
                <input type="hidden" id="is_active_group_chat_window" value="no" />
                <button type="button" name="group_chat" id="group_chat" class="btn btn-warning btn-xs">Group Chat</button>
            </div>
        </div>
        <div class="table-responsive">
            <div id="user_details"></div>
            <div id="user_model_details"></div>
        </div>
    </div>
    <button class="toggle-switch" onclick="toggleDarkMode()">Toggle Dark Mode</button>
    
    <!-- JavaScript libraries -->
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdn.rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.js"></script>
    <script>
        function updateDateTime() {
            var now = new Date();
            var datetime = now.toLocaleString();
            document.getElementById("datetime").innerHTML = datetime;
        }
        setInterval(updateDateTime, 1000);
        
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
        }
        
        $(document).ready(function(){
            fetch_user();
            setInterval(function(){
                update_last_activity();
                fetch_user();
                update_chat_history_data();
                fetch_group_chat_history();
            }, 5000);

            function fetch_user() {
                $.ajax({
                    url:"fetch_user.php",
                    method:"POST",
                    success:function(data){
                        $('#user_details').html(data);
                    }
                })
            }

            function update_last_activity() {
                $.ajax({
                    url:"update_last_activity.php",
                    success:function() {}
                })
            }

            function make_chat_dialog_box(to_user_id, to_user_name) {
                var modal_content = '<div id="user_dialog_'+to_user_id+'" class="user_dialog" title="You have chat with '+to_user_name+'">';
                modal_content += '<div style="height:400px; border:1px solid #ccc; overflow-y: scroll; margin-bottom:24px; padding:16px;" class="chat_history" data-touserid="'+to_user_id+'" id="chat_history_'+to_user_id+'">';
                modal_content += fetch_user_chat_history(to_user_id);
                modal_content += '</div>';
                modal_content += '<div class="form-group">';
                modal_content += '<textarea name="chat_message_'+to_user_id+'" id="chat_message_'+to_user_id+'" class="form-control chat_message"></textarea>';
                modal_content += '</div><div class="form-group" align="right">';
                modal_content+= '<button type="button" name="send_chat" id="'+to_user_id+'" class="btn btn-info send_chat">Send</button></div></div>';
                $('#user_model_details').html(modal_content);
            }

            $(document).on('click', '.start_chat', function(){
                var to_user_id = $(this).data('touserid');
                var to_user_name = $(this).data('tousername');
                make_chat_dialog_box(to_user_id, to_user_name);
                $("#user_dialog_"+to_user_id).dialog({
                    autoOpen:false,
                    width:400
                });
                $('#user_dialog_'+to_user_id).dialog('open');
                $('#chat_message_'+to_user_id).emojioneArea({
                    pickerPosition:"top",
                    toneStyle: "bullet"
                });
            });

            $(document).on('click', '.send_chat', function(){
                var to_user_id = $(this).attr('id');
                var chat_message = $.trim($('#chat_message_'+to_user_id).val());
                if(chat_message != '')
                {
                    $.ajax({
                        url:"insert_chat.php",
                        method:"POST",
                        data:{to_user_id:to_user_id, chat_message:chat_message},
                        success:function(data){
                            $('#chat_history_'+to_user_id).html(data);
                            $('#chat_message_'+to_user_id).val('');
                            $('#chat_message_'+to_user_id).emojioneArea().setText('');
                        }
                    })
                }
                else
                {
                    alert('Type something');
                }
            });

            function fetch_user_chat_history(to_user_id) {
                $.ajax({
                    url:"fetch_user_chat_history.php",
                    method:"POST",
                    data:{to_user_id:to_user_id},
                    success:function(data){
                        $('#chat_history_'+to_user_id).html(data);
                    }
                })
            }

            function update_chat_history_data() {
                $('.chat_history').each(function(){
                    var to_user_id = $(this).data('touserid');
                    fetch_user_chat_history(to_user_id);
                });
            }

            $(document).on('click', '.ui-button-icon', function(){
                $('.user_dialog').dialog('destroy').remove();
                $('#is_active_group_chat_window').val('no');
            });

            $(document).on('focus', '.chat_message', function(){
                var is_type = 'yes';
                $.ajax({
                    url:"update_is_type_status.php",
                    method:"POST",
                    data:{is_type:is_type},
                    success:function() {}
                })
            });

            $(document).on('blur', '.chat_message', function(){
                var is_type = 'no';
                $.ajax({
                    url:"update_is_type_status.php",
                    method:"POST",
                    data:{is_type:is_type},
                    success:function() {}
                })
            });

            $('#group_chat_dialog').dialog({
                autoOpen:false,
                width:400
            });

            $('#group_chat').click(function(){
                $('#group_chat_dialog').dialog('open');
                $('#is_active_group_chat_window').val('yes');
                fetch_group_chat_history();
            });

            $('#send_group_chat').click(function(){
                var chat_message = $.trim($('#group_chat_message').html());
                var action = 'insert_data';
                if(chat_message != '')
                {
                    $.ajax({
                        url:"group_chat.php",
                        method:"POST",
                        data:{chat_message:chat_message, action:action},
                        success:function(data){
                            $('#group_chat_message').html('');
                            $('#group_chat_history').html(data);
                        }
                    })
                }
                else
                {
                    alert('Type something');
                }
            });

            function fetch_group_chat_history() {
                var group_chat_dialog_active = $('#is_active_group_chat_window').val();
                var action = "fetch_data";
                if(group_chat_dialog_active == 'yes')
                {
                    $.ajax({
                        url:"group_chat.php",
                        method:"POST",
                        data:{action:action},
                        success:function(data)
                        {
                            $('#group_chat_history').html(data);
                        }
                    })
                }
            }

            $('#uploadFile').on('change', function(){
                $('#uploadImage').ajaxSubmit({
                    target: "#group_chat_message",
                    resetForm: true
                });
            });

            $(document).on('click', '.remove_chat', function(){
                var chat_message_id = $(this).attr('id');
                if(confirm("Are you sure you want to remove this chat?"))
                {
                    $.ajax({
                        url:"remove_chat.php",
                        method:"POST",
                        data:{chat_message_id:chat_message_id},
                        success:function(data)
                        {
                            update_chat_history_data();
                        }
                    })
                }
            });
            
        });  
    </script>
</body>
</html>
