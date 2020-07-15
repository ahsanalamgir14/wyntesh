<template>
  <el-row>
    <el-col  :xs="24" :sm="24" :md="10" :lg="10" :xl="10" >
      <div class="welcome-container"> 
        <div class="logo-text">
          <img  v-if="logo" :src="logo" class="sidebar-logo">
          <h2>Welcome to {{settings.company_name}}</h2>
        </div>
        <!-- <div class="footer">
          <a href="http://infex.in" target="_self">Home</a>
          <a href="http://infex.in" target="_self">Contact</a>
        </div> -->
      </div>
      
    </el-col>
    <el-col  :xs="24" :sm="24" :md="14" :lg="14" :xl="14" >
      <div class="register-container">

        <el-form ref="registerForm" :model="registerForm" :rules="registerRules" class="register-form" auto-complete="on" label-position="left">
          <h3 class="title">
            Register
          </h3>
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="sponsor_code">
                <span class="svg-container">
                  <i class="fas fa-users"></i>
                </span>
                <el-input v-model="registerForm.sponsor_code" v-on:blur="handleCheckSponsorCode()" name="sponsor_code" type="text" auto-complete="on" placeholder="Enter sponsor code" />
              </el-form-item>
            </el-col>

            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="sponsor_name">
                <span class="svg-container">
                  <i class="fas fa-user"></i>
                </span>
                <el-input v-model="registerForm.sponsor_name" disabled name="sponsor_name" type="text" auto-complete="on" placeholder="Sponsor name." />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="parent_code" >
                <span class="svg-container">
                  <i class="fas fa-users"></i>
                </span>               
                <el-input v-model="registerForm.parent_code" v-on:blur="handleCheckParentCode()" name="parent_code" type="text" auto-complete="on" placeholder="Enter Parent code" />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item prop="parent_name" >
                <span class="svg-container">
                  <i class="fas fa-user"></i>
                </span>
                <el-input v-model="registerForm.parent_name" disabled name="parent_name" type="text" auto-complete="on" placeholder="Parent name." />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >      
              <el-form-item prop="position"  class="radio-btn">

                <el-radio-group v-model="registerForm.position">
                    <el-radio border label="1">Leg A</el-radio>
                    <el-radio border label="2">Leg B</el-radio>
                    <el-radio border label="3">Leg C</el-radio>
                    <el-radio border label="4">Leg D</el-radio>
                  </el-radio-group>
              </el-form-item>
            </el-col>
          </el-row>
            <hr style="margin-bottom: 20px;width:50%;">
            <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="name">
                <span class="svg-container">
                  <i class="fas fa-user"></i>
                </span>
                <el-input v-model="registerForm.name" name="name" type="text" auto-complete="on" placeholder="Enter Name." />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="email">
                <span class="svg-container">
                  <i class="fas fa-envelope"></i>
                </span>
                <el-input v-model="registerForm.email" name="email" type="text" auto-complete="on" placeholder="Enter valid email." />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="password">
                <span class="svg-container">
                  <i class="fas fa-lock"></i>
                </span>
                 <el-input
                    v-model="registerForm.password" 
                    :type="pwdType"
                    name="password"
                    auto-complete="on"
                    placeholder="Password"
                  />
                  <span class="show-pwd" @click="showPwd">
                    <svg-icon icon-class="eye" />
                  </span>
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="contact">
                <span class="svg-container">
                  <i class="fas fa-phone-square"></i>
                </span>
                <el-input v-model="registerForm.contact" name="contact" type="text" auto-complete="on" placeholder="Enter valid contact." />
              </el-form-item>
            </el-col>
             <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="gender"  class="radio-btn">

                <el-radio-group v-model="registerForm.gender">
                    <el-radio border label="m">Male</el-radio>
                    <el-radio border label="f">Female</el-radio>
                  </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >      
              <el-form-item prop="dob" class="dob-input">
                <el-date-picker
                    v-model="registerForm.dob"
                    type="date"
                    format="yyyy-MM-dd"
                    value-format="yyyy-MM-dd"
                    placeholder="Date of birth">
                  </el-date-picker>
              </el-form-item>
            </el-col>
          </el-row>
          <el-form-item class="item-btn">
            <el-button :loading="loading"  icon="el-icon-position" type="primary"  @click.native.prevent="register">
              Register
            </el-button>
          </el-form-item>  
          <el-form-item class="register-btn">
            <router-link to="/login">Already a member ?<span> Login here.</span></router-link>
          </el-form-item>     
        </el-form>
      </div>
    </el-col>
    
  </el-row>
</template>

<script>
import { validEmail } from '@/utils/validate';
import logo from '@/assets/images/logo.png'
import { checkSponsorCode,registerMember } from "@/api/user/members";
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
    const validateContact  = (rule, value, callback) => {
      var pattern = /^\d*(?:\.\d{1,2})?$/;
      if (!pattern.test(value)) {
        callback(new Error('Enter valid 10 digit contact number.'));
      } else {
        callback();
      }
    };
    const validatePass = (rule, value, callback) => {
      if (value) {
        if(value.length < 4){
          callback(new Error('Password cannot be less than 4 characters'));  
        }else{
          callback();
        }        
      } else {
        callback(new Error('Password is required.'));  
      }
    };
    return {
      registerForm: {
        sponsor_code:undefined,
        sponsor_name:undefined,
        parent_code:undefined,
        parent_name:undefined,
        name: undefined,
        email: undefined,
        password: undefined,
        contact:undefined,
        dob:undefined,
        position:'1',
        gender:'m'
      },
      logo: logo,
      settings:{},
      registerRules: {
        name: [{ required: true, trigger: 'blur', message: 'Name is required', }],
        email: [{ required: true, trigger: 'blur', validator: validateEmail  }],
        password: [{ required: true, trigger: 'blur', validator: validatePass }],
        contact: [{ required: true, trigger: 'blur', validator: validateContact  }],
      },
      loading: false,
      pwdType: 'password',
      redirect: undefined,
    };
  },

  created(){
    this.getRecaptcha();
    this.registerForm.sponsor_code=this.$route.query.sponsor_code
    this.handleCheckSponsorCode(this.$route.query.sponsor_code);
    getPublicSettings().then(response => {
      this.settings = response.data;
    });
  },
  methods: {
    async getRecaptcha(){
      await this.$recaptchaLoaded();
      const token = await this.$recaptcha('addmember');
      this.registerForm.recaptcha=token;      
    },
    showPwd() {
      if (this.pwdType === 'password') {
        this.pwdType = '';
      } else {
        this.pwdType = 'password';
      }
    },
    resetRegistraionForm(){
      this.registerForm= {
        sponsor_code:undefined,
        sponsor_name:undefined,
        parent_code:undefined,
        parent_name:undefined,
        name: undefined,
        email: undefined,
        password: undefined,
        contact:undefined,
        dob:undefined,
        position:'1',
        gender:'m'
      };
    },
    handleCheckSponsorCode(){
      if(this.registerForm.sponsor_code){
        checkSponsorCode(this.registerForm.sponsor_code).then((response) => {
          this.registerForm.sponsor_name=response.data.name;          
        })
      }            
    },
    handleCheckParentCode(){
      if(this.registerForm.parent_code){
        checkSponsorCode(this.registerForm.parent_code).then((response) => {
          this.registerForm.parent_name=response.data.name;          
        })
      }            
    },
    register() {
      this.$refs.registerForm.validate(valid => {
        if (valid) {
          this.loading = true;
          registerMember(this.registerForm).then((response) => {
            this.loading=false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })
            this.getRecaptcha();
            this.resetRegistraionForm();
            this.$router.push('/login' );
          })
          .catch((error) => {
              this.getRecaptcha();
              this.loading = false;
            });
        } 
      });
    },
  },
};
</script>

<style rel="stylesheet/scss" lang="scss">
$bg:#2d3a4b;
$light_gray:#eee;
$dark_gray:#889aa4;



@media only screen and (max-width: 500px) {
    .welcome-container{
        height: 25vh !important; 
        img{
            height:20%;
            width:57% !important;
        }
        .logo-text{
            text-align: center;
            padding-top: 5% !important;
        }
    }
    .login-container {
        .login-form {
            width: 520px;
            max-width: 100%;
            padding: 35px 35px 15px 35px;
            margin: 3px auto !important;
        }
    }
    
}

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
.register-container {
 
  .el-input {
    display: inline-block;
    height: 47px;
    width: 85%;
    input {
      background: transparent;
      border: 0px;
      -webkit-appearance: none;
      border-radius: 0px;
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

    .svg-container {
        padding: 6px 5px 6px 15px;
        color: $dark_gray;
        vertical-align: middle;
        width: 30px;
        display: inline-block;
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
  }
  .item-btn{
    border:none;
    width:100%;
    .el-button{
      float:right;  
    }
    
  }
  .radio-btn{
    border:none;
    margin-top: 10px;
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
.register-container {
  el-row{
    el-col{

     
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
      
     
      
      .set-language {
        color: #fff;
        position: absolute;
        top: 40px;
        right: 35px;
      }
    }
  } 
   .title {
        font-size: 26px;
        font-weight: 400;
        color: #67666e;
        margin: 0px auto 40px auto;
        text-align: center;
        font-weight: bold;
      }
        .register-form {

        max-width: 100%;
        padding: 35px 35px 15px 35px;
        margin: 0 auto;
      }
 
}
</style>
