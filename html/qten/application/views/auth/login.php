<?php
/**
 * Created by PhpStorm.
 * User: ntwDeveloper
 * Date: 2018-05-29
 * Time: 오후 4:12
 */?>
<div class="container">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="col-md-12">
                <h3 class="page-header">qten 백오피스 </h3>
            </div>
            <div class="login-panel panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Please Sign In</h3>
                </div>
                <div class="panel-body">
                    <form role="form" method="POST" action="<?php echo site_url('auth/login/login_check'); ?>" onsubmit="return chkLoginForm()">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="User ID" name="user_id" type="text" autofocus>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="passwd" type="password" value="">
                            </div>
                            <!--<div class="checkbox">
                                <label>
                                    <input name="remember" type="checkbox" value="Remember Me">Remember Me
                                </label>
                            </div>-->
                            <!-- Change this to a button or input when using this as a form -->
                            <button type="submit" class="btn btn-lg btn-success btn-block">Login</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    function chkLoginForm() {
        if($(":input[name=user_id]").val().trim() == ''){
            alert('Empty User iD value.');
            $(":input[name=user_id]").val().focus();
            return false;
        }
        if($(":input[name=passwd]").val().trim() == ''){
            alert('Empty Password value.');
            $(":input[name=passwd]").val().focus();
            return false;
        }
        return true;
    }
</script>