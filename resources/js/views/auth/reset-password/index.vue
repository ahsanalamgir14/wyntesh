<template>
  <el-row>
    <el-col  :xs="24" :sm="24" :md="10" :lg="10" :xl="10" >
      <div class="welcome-container"> 
        <div class="logo-text">
          <img  v-if="logo" :src="logo" class="sidebar-logo">
          <h2>Welcome to MLM World</h2>
        </div>
        <div class="footer">
          <a href="http://infex.in" target="_self">Home</a>
          <a href="http://infex.in" target="_self">Contact</a>
        </div>
      </div>
      
    </el-col>
    <el-col  :xs="24" :sm="24" :md="14" :lg="14" :xl="14" >
      <div class="login-container">
        <el-form ref="resetPasswordForm" :model="resetPasswordForm" :rules="passwordResetRules" class="login-form" auto-complete="on" label-position="left">
          <h3 class="title">
            Reset Password
          </h3>
          <el-form-item prop="username">
            <span class="svg-container">
              <svg-icon icon-class="user" />
            </span>
            <el-input v-model="resetPasswordForm.username" 
             @keyup.enter.native="handleResetPassword"
            name="username" type="text" auto-complete="on" placeholder="Enter Username/ID" />
          </el-form-item>
          <el-form-item prop="password">
            <span class="svg-container">
              <svg-icon icon-class="password" />
            </span>
            <el-input
              v-model="resetPasswordForm.password"LangSelect
              :type="pwdType"
              name="password"
              auto-complete="on"
              placeholder="Password"
              @keyup.enter.native="handleResetPassword"
            />
            <span class="show-pwd" @click="showPwd">
              <svg-icon icon-class="eye" />
            </span>
          </el-form-item>
          <el-form-item prop="password_confirmation">
            <span class="svg-container">
              <svg-icon icon-class="password" />
            </span>
            <el-input
              v-model="resetPasswordForm.password_confirmation" LangSelect
              :type="pwdType"
              name="password"
              auto-complete="on"
              placeholder="Confirm Password"
              @keyup.enter.native="handleResetPassword"
            />
            <span class="show-pwd" @click="showPwd">
              <svg-icon icon-class="eye" />
            </span>
          </el-form-item> 

          <el-form-item class="item-btn">
            <el-button :loading="loading"  icon="el-icon-unlock" type="primary"  @click.native.prevent="handleResetPassword">
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
import { resetPassword } from "@/api/auth";

export default {
  name: 'Login',
  data() {
    const validatePass = (rule, value, callback) => {
      if (value.length < 4) {
        callback(new Error('Password cannot be less than 4 digits'));
      } else {
        callback();
      }
    };
    const validateEmail = (rule, value, callback) => {
      if (!validEmail(value)) {
        callback(new Error('Please enter the correct email'));
      } else {
        callback();
      }
    };
  
    return {
      resetPasswordForm: {
        password: '',
        token:undefined,
        username:undefined,
        password_confirmation:'',
      },
      logo: logo,
      passwordResetRules: {
        password: [{ required: true, trigger: 'blur',  validator: validatePass }],
        password_confirmation: [{ required: true, trigger: 'blur',  validator: validatePass }],
        username: [{ required: true, trigger: 'blur', message:'Username is required.'  }],
      },
      loading: false,
      pwdType: 'password',
    };
  },
  created(){
    this.resetPasswordForm.token=this.$route.query.token
  },
  methods: {
   showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = '';
      } else {
        this.pwdType = 'password';
      }
    },
    handleResetPassword() {
      this.$refs.resetPasswordForm.validate(valid => {
        if (valid) {
          this.loading = true;
            resetPassword(this.resetPasswordForm).then((response) => {
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
    width:20%;
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
