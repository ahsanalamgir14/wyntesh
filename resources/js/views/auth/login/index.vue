<template>
  <el-row>
     <!-- <el-dialog width="50%" class="popups" :fullscreen="is_mobile"  height="700px" :visible.sync="dialogWinnerPupup">
        <div v-if="popup">
          <img :src="popup[0].image" class="img" max-height="500px;" />
        </div>
      </el-dialog> -->
      <div v-if="popup">
        <div v-if="dialogWinnerPupup" class="simplePopupBackground">
            <div id="onstartmodal" class="simplePopup">
                <div id="close" class="simplePopupClose" @click="dialogWinnerPupup=false" >X</div>
                <img class="img-responsive" :src="popup.image" alt="">
            </div> 
        </div>
      </div>
    <el-col :xs="24" :sm="24" :md="10" :lg="10" :xl="10">
      <div class="welcome-container">
        <div class="logo-text">
          <img v-if="logo" :src="logo" class="sidebar-logo" style="margin:0 auto;">
          <h2>Welcome to {{ settings.company_name }}</h2>
        </div>
        <!-- <div class="footer">
          <a href="http://infex.in" target="_self">Home</a>
          <a href="http://infex.in" target="_self">Contact</a>
        </div> -->
      </div>

    </el-col>
    <el-col :xs="24" :sm="24" :md="14" :lg="14" :xl="14">
      <div class="login-container">
        <el-form ref="loginForm" :model="loginForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">
          <h3 class="title">
            {{ $t('login.title') }}
          </h3>
          <el-form-item prop="username">
            <span class="svg-container">
              <svg-icon icon-class="user" />
            </span>
            <el-input v-model="loginForm.username" name="username" type="text" auto-complete="on" :placeholder="$t('login.username')" />
          </el-form-item>
          <el-form-item prop="password">
            <span class="svg-container">
              <svg-icon icon-class="password" />
            </span>
            <el-input
              v-model="loginForm.password"
              lang-select
              :type="pwdType"
              name="password"
              auto-complete="on"
              placeholder="Password"
              @keyup.enter.native="handleLogin"
            />
            <span class="show-pwd" @click="showPwd">
              <svg-icon icon-class="eye" />
            </span>
          </el-form-item>
          <el-form-item class="item-btn">
            <router-link to="/forgot-password">Forgot Password ?</span></router-link>
            <el-button :loading="loading" icon="el-icon-unlock" type="primary" @click.native.prevent="handleLogin">
              Sign in
            </el-button>
          </el-form-item>
          <el-form-item class="register-btn">
            <router-link to="/register">Are you not a member yet ? <span>Register here.</span></router-link>
          </el-form-item>
        </el-form>
      </div>
    </el-col>

  </el-row>
</template>

<script>
import { validEmail } from '@/utils/validate';
import logo from '@/assets/images/logo.png';
import { getPublicSettings } from '@/api/user/settings';
import { getSoftwarePopup } from '@/api/admin/software-popups';

export default {
  name: 'Login',
  data() {
    const validateEmail = (rule, value, callback) => {
      if (!validEmail(value)) {
        callback(new Error('Please enter the correct email'));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (value.length < 4) {
        callback(new Error('Password cannot be less than 4 digits'));
      } else {
        callback();
      }
    };
    return {
      loginForm: {
        username: '',
        password: '',
      },
      logo: logo,
      loginRules: {
        username: [{ required: true, trigger: 'blur', message: 'Username is required' }],
        password: [{ required: true, trigger: 'blur', validator: validatePass }],
      },
      is_mobile:false,
      dialogWinnerPupup: false,
      popup:{
        image:null
      },
      settings: {},
      loading: false,
      pwdType: 'password',
      redirect: undefined,
    };
  },
  watch: {
    $route: {
      handler: function(route) {
        this.redirect = route.query && route.query.redirect;
      },
      immediate: true,
    },
  },
  created() {

    let username=this.$route.query.username;
    let token=this.$route.query.token;

    if(username && token){
      this.loginForm.username=username;
      this.loginForm.token=token;
      this.handleAdminLogin();
    }

    getSoftwarePopup().then(response => {
       if(response.data)
          this.popup.image=null;
        this.popup = response.data;
        if(this.popup){
           this.popupsOpen();
        }
    });

     if(window.screen.width <= '550'){
      this.is_mobile=true;
    }

    getPublicSettings().then(response => {
      this.settings = response.data;
    });
  },
  methods: {
    showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = '';
      } else {
        this.pwdType = 'password';
      }
    },
    handleAdminLogin() {
      this.loading = true;
      this.$store.dispatch('user/admin_login', this.loginForm)
        .then(() => {
          this.$router.push({ path: this.redirect || '/' });
          this.loading = false;
          const recaptcha = this.$recaptchaInstance;
          recaptcha.hideBadge()
        })
        .catch((error) => {
          this.loading = false;
        });
    },
    popupsOpen() {
      var self = this;
      setTimeout(function() { 
        self.dialogWinnerPupup=true;
      }, 1000);
    },
    handleLogin() {
      this.$refs.loginForm.validate(valid => {
        if (valid) {
          this.loading = true;
          this.$store.dispatch('user/login', this.loginForm)
            .then(() => {
              this.$router.push({ path: this.redirect || '/' });
              this.loading = false;
              const recaptcha = this.$recaptchaInstance;
              recaptcha.hideBadge();
            })
            .catch((error) => {
              this.loading = false;
            });
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
$bg:#2d3a4b;
$light_gray:#eee;

  @media (min-width: 768px) {
    .simplePopup {
        left: 50%;
    }
  }

  @media (max-width: 768px) {
    .simplePopup {
        width: calc(100% - 30px);
        height: calc(100% - 50px);
    }
  }
  .simplePopup img {
      width: calc(100%);
      height: auto;
  }

@media only screen and (max-width: 500px) {
    .welcome-container{
        height: 55vh !important; 
        .logo-text{
            text-align: center;
            padding-top: 40% !important;
        }
    }
    .login-container {
        .login-form {
            width: 520px;
            max-width: 100%;
            padding: 35px 35px 15px 35px !important;
            margin: 3px auto !important;
        }
    }
}
// Popup Css
.simplePopupBackground {
    background: rgba(0, 0, 0, 0.7);
    position: fixed;
    height: 100%;
    width: 100%;
    top: 0;
    left: 0;
    z-index: 300001;
    overflow: hidden;
}
.simplePopup {
    padding: 0;
    border-radius: 5px;
    overflow: hidden;
    height: 80%;
    /* max-height: calc(100% - 40px); */
}
.simplePopup {
    position: fixed;
    z-index: 300001;
    color: #414141;
    padding: 20px;
    border-radius: 8px;
    left: 50%;
    top: 50%!important;
    -ms-transform: translate(-50%, -50%);
    transform: translate(-50%, -50%);
    -webkit-transform: translate(-50%, -50%);
}

.simplePopupClose {
    cursor: pointer;
    font-size: 0;
    position: absolute;
    cursor: pointer;
    top: 25px;
    right: 25px;
    width: 30px;
    height: 30px;
}

.simplePopupClose:before,
.simplePopupClose:after {
    content: '';
    top: 0px;
    width: 2px;
    height: 30px;
    background: rgb(255, 255, 255);
    display: block;
    position: absolute;
    cursor: pointer;
    left: 50%;
    margin-left: -1px;
}

.simplePopup img {
    vertical-align: middle;
    margin-left: auto;
    margin-right: auto;
}

.simplePopup img {
    border: 0;
    height: 100%;
}

.simplePopupClose:after {
    -ms-transform: rotate(+45deg);
    transform: rotate(+45deg);
    -webkit-transform: rotate(+45deg);
}

.simplePopupClose:before {
    -ms-transform: rotate(-45deg);
    transform: rotate(-45deg);
    -webkit-transform: rotate(-45deg);
}
.welcome-container{
  width: 100%;
  height: 100vh;
  padding: 25px 25px 25px 25px;
  background: url('~@/assets/images/bg-4.jpg');
  .logo-text{
    text-align: center;
    padding-top: 45%;
  }
  img{

    height:20%;
    width:57%;
  }
  h2{
    color:#fff;
  }
  .footer{
    color:#fff;
    position:absolute;
    top:20px;
    a{
      margin-right:10px;
      margin-left:10px;
    }
  }

}

/* reset element-ui css */
.login-container {
  .el-input {
    display: inline-block;
    height: 47px;
    width: 85%;
    input {
      background: transparent;
      border: 0px;
      -webkit-appearance: none;
      border-radius: 0px;
      padding: 12px 5px 12px 15px;
      color: #495057;
      height: 47px;

      &:-webkit-autofill {
        -webkit-box-shadow: 0 0 0px 1000px #fff inset !important;
        -webkit-text-fill-color: #454545 !important;
      }
    }
  }
  .el-form-item {
    border: 1px solid rgb(212, 211, 211);
    border-radius: 5px;
    color: #454545;
    margin-bottom: 20px;
  }
  .item-btn{
    border:none;
    width:100%;
    .el-button{
      float:right;
    }

  }
  .register-btn{
    border:none;
    text-align:center;
    span{
      color:#409EFF;
      font-weight:bold;
    }
  }
}

</style>

<style rel="stylesheet/scss" lang="scss" scoped>
$bg:#2d3a4b;
$dark_gray:#889aa4;
$light_gray:#eee;
.login-container {

  .login-form {

    width: 520px;
    max-width: 100%;
    padding: 35px 35px 15px 35px;
    margin: 120px auto;
  }
  .tips {
    font-size: 14px;
    color: #fff;
    margin-bottom: 10px;
    span {
      &:first-of-type {
        margin-right: 16px;
      }
    }
  }
  .svg-container {
    padding: 6px 5px 6px 15px;
    color: $dark_gray;
    vertical-align: middle;
    width: 30px;
    display: inline-block;
  }
  .title {
    font-size: 26px;
    font-weight: 400;
    color: #67666e;
    margin: 0px auto 40px auto;
    text-align: center;
    font-weight: bold;
  }
  .show-pwd {
    position: absolute;
    right: 10px;
    top: 7px;
    font-size: 16px;
    color: $dark_gray;
    cursor: pointer;
    user-select: none;
  }
  .set-language {
    color: #fff;
    position: absolute;
    top: 40px;
    right: 35px;
  }


}
</style>
