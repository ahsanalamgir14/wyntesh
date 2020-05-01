<template>
  <div class="app-container">
    <el-tabs type="border-card">
      <el-tab-pane label="Add Member">
        <el-form ref="registerForm" :model="registerForm" :rules="registerRules" class="register-form" auto-complete="on" label-position="left">          
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >      
              <el-form-item prop="sponsor_code" label="Spondor Code">
               
                <el-input v-model="registerForm.sponsor_code" v-on:blur="handleCheckSponsorCode()" name="sponsor_code" type="text" auto-complete="on" placeholder="Enter sponsor code" />
              </el-form-item>
              <el-form-item prop="sponsor_name" label="Spondor Name">
                
                <el-input v-model="registerForm.sponsor_name" disabled name="sponsor_name" type="text" auto-complete="on" placeholder="Sponsor name." />
              </el-form-item>
              <el-form-item prop="position"  class="radio-btn" label="Position">
                </br>
                <el-radio-group v-model="registerForm.position" >                  
                    <el-radio border label="1">Left</el-radio>
                    <el-radio border label="2">Right</el-radio>
                  </el-radio-group>
              </el-form-item>
            </el-col>

            <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >      
              <el-form-item prop="name" label="Member Name">             
                <el-input v-model="registerForm.name" name="name" type="text" auto-complete="on" placeholder="Enter Name." />
              </el-form-item>
              <el-form-item prop="email" label="Member Email">               
                <el-input v-model="registerForm.email" name="email" type="text" auto-complete="on" placeholder="Enter valid email." />
              </el-form-item>
              <el-form-item prop="password" label="Password">
               
                 <el-input
                    v-model="registerForm.password" 
                    :type="pwdType"
                    name="password"
                    auto-complete="on"
                    placeholder="Password"
                  />
              </el-form-item>
            </el-col>
            <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >      
              <el-form-item prop="gender"  class="radio-btn" label="Gender">
                </br>
                <el-radio-group v-model="registerForm.gender">
                    <el-radio border label="m">Male</el-radio>
                    <el-radio border label="f">Female</el-radio>
                  </el-radio-group>
              </el-form-item>
              <el-form-item prop="dob" class="dob-input" label="Date of Birth">
                </br>
                <el-date-picker
                    v-model="registerForm.dob"
                    type="date"
                    format="yyyy-MM-dd"
                    value-format="yyyy-MM-dd"
                    placeholder="Date of birth">
                  </el-date-picker>
              </el-form-item>
              <el-form-item prop="contact" label="Contact No">
               
                <el-input v-model="registerForm.contact" name="contact" type="text" auto-complete="on" placeholder="Enter valid contact." />
              </el-form-item>
            </el-col>
          </el-row>           
          <el-form-item class="item-btn">
            <el-button :loading="loading"  icon="el-icon-position" type="primary"  @click.native.prevent="register">
              Add Member
            </el-button>
          </el-form-item>  
          
        </el-form>
      </el-tab-pane>
    </el-tabs>      
  </div>
</template>

<script>
import { checkSponsorCode,addMember } from "@/api/admin/members";
import defaultSettings from '@/settings';
const { totalLegs } = defaultSettings;
import waves from "@/directive/waves"; // waves directive
import { validEmail } from '@/utils/validate';

export default {
  name: "AddMember",
  async created() {        
    await this.getRecaptcha();
    let sponsor_code=this.$route.query.sponsor_code;
    let position=this.$route.query.position;
    this.registerForm.sponsor_code=sponsor_code;
    if(sponsor_code){
      checkSponsorCode(sponsor_code).then((response) => {
        this.registerForm.sponsor_name=response.data;                
      })  
    }
    if(position && position <= totalLegs){
     this.registerForm.position=position; 
    }
    
  },
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
        name: undefined,
        email: undefined,
        password: undefined,
        contact:undefined,
        dob:undefined,
        position:'1',
        gender:'m'
      },
      registerRules: {
        sponsor_code: [{ required: true, trigger: 'blur', message: 'Sponsor code is required', }],
        name: [{ required: true, trigger: 'blur', message: 'Name is required', }],
        email: [{ required: true, trigger: 'blur', validator: validateEmail  }],
        password: [{ required: true, trigger: 'blur', validator: validatePass }],
        contact: [{ required: true, trigger: 'blur', validator: validateContact  }],
        position: [
            { required: true, message: 'Please select position', trigger: 'change' }
          ],
      },
      loading: false,
      pwdType: 'password',
      redirect: undefined,
    };
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
        name: undefined,
        email: undefined,
        password: undefined,
        contact:undefined,
        dob:undefined,
        position:'1',
        gender:'m',
        recaptcha:'',
      };
    },
    handleCheckSponsorCode(){
      if(this.registerForm.sponsor_code){
        checkSponsorCode(this.registerForm.sponsor_code).then((response) => {
          this.registerForm.sponsor_name=response.data;          
        })
      }            
    },
    register() {
      this.$refs.registerForm.validate(valid => {
        if (valid) {
          this.loading = true;
          addMember(this.registerForm).then((response) => {
            this.loading=false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })
            this.getRecaptcha();
            this.resetRegistraionForm();
           // this.$router.push('/login' );
          })
          .catch((error) => {             
              this.loading = false;
              this.getRecaptcha();
            });
        } 
      });
    },
  },
};
</script>

<style scoped>
.item-btn{
  margin-top: 20px;
}
</style>
