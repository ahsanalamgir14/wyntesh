<template>
  <el-row>
    <el-col  :xs="24" :sm="24" :md="10" :lg="10" :xl="10" >
      <div class="welcome-container"> 
        <div class="logo-text">
          <img  v-if="logo" :src="logo" class="sidebar-logo" style="margin:0 auto;">
          <h2>Welcome to {{settings.company_name}}</h2>
        </div>
        <!-- <div class="footer">
          <a href="http://infex.in" target="_self">Home</a>
          <a href="http://infex.in" target="_self">Contact</a>
        </div> -->
      </div>
      
    </el-col>
    <el-col  :xs="24" :sm="24" :md="14" :lg="14" :xl="14" >
      <div class="login-container">
        <el-form ref="forgotPassForm" :model="forgotPassForm" :rules="loginRules" class="login-form" auto-complete="on" label-position="left">
          <h3 class="title">
            Forgot Password
          </h3>      
          <el-form-item prop="username">
            <span class="svg-container">
              <svg-icon icon-class="user" />
            </span>
            <el-input v-model="forgotPassForm.username" name="username" type="text" auto-complete="on" placeholder="Enter Username/ID" @keyup.enter.native="handleForgotPassword"  />
          </el-form-item>
          <el-form-item class="item-btn">
            <el-button :loading="loading"  icon="el-icon-unlock" type="primary"  @click.native.prevent="handleForgotPassword">
              Reset Password
            </el-button>
          </el-form-item>  
          <el-form-item class="register-btn">
            <router-link to="/login">Remember your password ?<span> Login here.</span></router-link>
          </el-form-item>     
        </el-form>
      </div>
    </el-col>
    
  </el-row>
</template>

<script>
import { validEmail } from '@/utils/validate';
import logo from '@/assets/images/logo.png'
import { getResetToken } from "@/api/auth";
import { getPublicSettings } from "@/api/user/settings";

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

    const validateUsername = (rule, value, callback) => {
      if (value.length < 2) {
        callback(new Error('Enter valid username'));
      } else {
        callback();
      }
    };
  
    return {
      forgotPassForm: {
        username: '',
      },
      settings:{},
      logo: logo,
      loginRules: {
        username: [{ required: true, trigger: 'blur', validator: validateUsername }],
      },
      loading: false,
      redirect: undefined,
    };
  },
  created() {
    getPublicSettings().then(response => {
      this.settings = response.data;
    });
  },
  methods: {
   
    handleForgotPassword() {
      this.$refs.forgotPassForm.validate(valid => {
        if (valid) {
          this.loading = true;
            getResetToken(this.forgotPassForm).then((response) => {
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })
            this.loading = false;
            this.$router.push('/login' );
          }).catch((err)=>{
            this.loading=false;
          })
        } 
      });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
$bg:#fff;
$light_gray:#eee;

.welcome-container{
  width: 100%;
  height: 100vh;
  padding: 25px 25px 25px 25px;
  background: url('~@/assets/images/bg-4.jpg');
  .logo-text{
    text-align: center;
    padding-top: 30%;
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
        -webkit-box-shadow: 0 0 0px 1000px $bg inset !important;
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
