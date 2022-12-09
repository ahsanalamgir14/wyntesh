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
    <el-dialog width="60%" top="5px" title="Terms and Conditions" height="700px" :visible.sync="showePupupDialog" >
     <el-row justify="center" style="margin-left: 10px;margin-right: 10px;">
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
              <div><p>1 . TERMS<br>By accessing the website at https://www.wyntash.in/, you are agreeing to be bound by these terms of service, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws. If you do not agree with any of these terms, you are prohibited from using or accessing this site. The materials contained in this website are protected by applicable copyright and trademark law.</p>
              <p>2 . USER LICENSE&nbsp;<br>Permission is granted to temporarily download one copy of the materials (information or software) on Wyntash's website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:
                  modify or copy the materials;
                  use the materials for any commercial purpose, or for any public display (commercial or non-commercial);
                  attempt to decompile or reverse engineer any software contained on Wyntash's website;
                  remove any copyright or other proprietary notations from the materials; or
                  transfer the materials to another person or "mirror" the materials on any other server.
                  This license shall automatically terminate if you violate any of these restrictions and may be terminated by Wyntash at any time. Upon terminating your viewing of these materials or upon the termination of this license, you must destroy any downloaded materials in your possession whether in electronic or printed format.</p>
              <p>3 . DISCLAIMER&nbsp;<br>The materials on Wyntash's website are provided on an 'as is' basis. Wyntash makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.Further, Wyntash does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its website or otherwise relating to such materials or on any sites linked to this site.</p>
              <p>4 . LIMITATIONS&nbsp;&nbsp;<br>In no event shall Wyntash or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on Wyntash's website, even if Wyntash or a Wyntash authorized representative has been notified orally or in writing of the possibility of such damage. Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.</p>
              <p>5 . ACCURACY OF MATERIALS&nbsp;&nbsp;<br>The materials appearing on Wyntash's website could include technical, typographical, or photographic errors. Wyntash does not warrant that any of the materials on its website are accurate, complete or current. Wyntash may make changes to the materials contained on its website at any time without notice. However Wyntash does not make any commitment to update the materials.</p>
              <p>6 . LINKS&nbsp;<br>Wyntash has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by Wyntash of the site. Use of any such linked website is at the user's own risk.</p>
              <p>7 . MODIFICATIONS&nbsp;<br>Wyntash may revise these terms of service for its website at any time without notice. By using this website you are agreeing to be bound by the then current version of these terms of service.</p>
              <p>8 . GOVERNING LAW&nbsp;&nbsp;<br>These terms and conditions are governed by and construed in accordance with the laws of India and you irrevocably submit to the exclusive jurisdiction of the courts in that State or location.</p>
              <p>CONTACT US&nbsp;&nbsp;<br>If you have any questions or suggestions about our Privacy Policy, do not hesitate to contact us.</p></div>
        </el-col>
          <!-- <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <img :src="softwarePupup.image" max-height="500px;" />
          </el-col> -->
      </el-row>
      <span slot="footer" class="dialog-footer">
        <el-button @click="showePupupDialog = false">Cancel</el-button>
         <el-button :loading="loading" class="font-semibold bg-purple-1 border-purple-1 hover:bg-purple-600 focus:bg-purple-600 hover:border-purple-600 focus:border-purple-600 focus:outline-none" icon="el-icon-position" type="primary" @click="showePupupDialog = false">
           Yes, I Agree
          </el-button>
      </span>
    </el-dialog>
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
       showePupupDialog:true,
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
            width:70% !important;
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
