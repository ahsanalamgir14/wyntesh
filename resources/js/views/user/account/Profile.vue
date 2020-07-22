<template>
  <div class="app-container">
    <el-row :gutter="20">
      <el-col :span="24">
        <el-alert
          v-if="temp.kyc.remarks && temp.kyc.verification_status=='rejected'"
          style="margin-bottom: 10px;"
          title="KYC Rejection"
          type="error"
          :description="temp.kyc.remarks"
          show-icon>
        </el-alert>
      </el-col>
    </el-row>
    <el-form  :model="temp">
      <el-row :gutter="20">
        <el-col  :xs="24" :sm="24" :md="12" :lg="6" :xl="6" >
          <el-card >
            <div class="user-profile">
              <div class="user-avatar box-center">
                <pan-thumb :image="temp.profile_picture?temp.profile_picture:'/images/avatar.png'" :height="'100px'" :width="'100px'" :hoverable="false" />
              </div>
              <div class="box-center">
                <div class="user-name text-center">
                  {{ temp.name }}
                  </br></br>

                  <el-tag v-if="temp.kyc.verification_status=='verified'" type="success">Verified</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='pending'" type="warning">KYC - Pending</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='submitted'" type="primary">KYC - Submitted</el-tag>
                  <el-tag v-if="temp.kyc.verification_status=='rejected'" type="danger">KYC - Rejected</el-tag>
                </div>
                <div class="user-role text-center text-muted">
                  <h4 style="margin-bottom:7px ">Joined on</h4>
                  {{ temp.created_at | parseTime('{y}-{m}-{d}') }}
                </div>
                <div style="margin-top:10px;">
                  <el-button type="warning" round v-clipboard:copy="referral_link" v-clipboard:success="onCopy" >Copy referral link</el-button>
                </div>                
              </div>
            </div>
          </el-card>
        </el-col>
        <el-col  :xs="24" :sm="24" :md="12" :lg="18" :xl="18" >
          <el-tabs v-model="activeActivity" type="border-card" >
            <el-tab-pane v-loading="updating" label="Basic Details" name="details">
              <el-row :gutter="20">
                <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                  <el-form-item label="Name">
                    <el-input v-model="temp.name"  />
                  </el-form-item>
                  <el-form-item label="Email">
                    <el-input disabled v-model="temp.email"  />
                  </el-form-item>
                  <el-form-item label="Username" prop="username">
                    <el-input disabled v-model="temp.username" />
                  </el-form-item>
                </el-col>
                <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                  
                  <el-form-item label="Contact" prop="contact">
                    <el-input v-model="temp.contact" />
                  </el-form-item>

                  <el-form-item label="Gender" prop="gender">
                    </br>
                    <el-radio-group v-model="temp.gender">
                      <el-radio label="m" border>Male</el-radio>
                      <el-radio label="f" border> Female</el-radio>
                    </el-radio-group>
                  </el-form-item>

                  <el-form-item label="DOB" prop="dob">
                    </br>
                    <el-date-picker
                      v-model="temp.dob"
                      type="date"
                      format="yyyy-MM-dd"
                      value-format="yyyy-MM-dd"
                      placeholder="Date of birth">
                    </el-date-picker>
                  </el-form-item>
                             
                </el-col>
              </el-row>
              <el-form-item>
                <el-button type="primary"  icon="el-icon-finished" :loading="buttonLoading" :disabled="temp.kyc.verification_status=='submitted'" @click="onSubmit">
                  Update
                </el-button>
              </el-form-item>
            </el-tab-pane>
            <el-tab-pane v-loading="updating" label="Kyc and Bank" name="kyc">
              <el-row :gutter="20">
                <el-col :span="12">            
                  
                  <el-form-item label="Adhar" prop="adhar">
                    <el-input max="16" v-model="temp.kyc.adhar" />
                  </el-form-item>
                 
                  <el-form-item label="City" prop="city">
                    <el-input  v-model="temp.kyc.city" />
                  </el-form-item>
                  <el-form-item label="State" prop="state">
                    <el-input v-model="temp.kyc.state" />
                  </el-form-item>
                  <el-form-item label="Address" prop="address">
                    <el-input type="textarea"
                      :rows="2"
                      placeholder="Address" v-model="temp.kyc.address" />
                  </el-form-item>
                  <el-form-item label="Pincode" prop="pincode">
                    <el-input v-model="temp.kyc.pincode" />
                  </el-form-item>

                </el-col>
                <el-col :span="12">
                   <el-form-item label="Pan" prop="pan">
                    <el-input max="10" v-model="temp.kyc.pan" />
                  </el-form-item>
                  <el-form-item label="Bank A/C Name" prop="bank_ac_name">
                    <el-input v-model="temp.kyc.bank_ac_name"  />
                  </el-form-item>
                  <el-form-item label="Bank Name" prop="bank_name">
                    <el-input v-model="temp.kyc.bank_name"  />
                  </el-form-item>
                  <el-form-item label="A/C No" prop="bank_ac_no">
                    <el-input v-model="temp.kyc.bank_ac_no"  />
                  </el-form-item>
                  <el-form-item label="IFSC Code" prop="ifsc">
                    <el-input v-model="temp.kyc.ifsc"  />
                  </el-form-item>
                </el-col>
              </el-row>
              <el-form-item>
                <el-button type="primary"  icon="el-icon-finished" :loading="buttonLoading" :disabled="temp.kyc.verification_status=='submitted'" @click="onSubmit">
                  Update
                </el-button>
              </el-form-item>
            </el-tab-pane>
            <el-tab-pane v-loading="updating" label="Nominee Details" name="nominee">
              <el-row :gutter="20">
                <el-col :span="12">            
                  
                  <el-form-item label="Nominee Name" prop="nominee_name">
                    <el-input max="64" v-model="temp.kyc.nominee_name" />
                  </el-form-item>
                  <el-form-item label="Nominee Relation" prop="nominee_relation" >
                    <br>
                    <el-select v-model="temp.kyc.nominee_relation" clearable placeholder="Select" style="width: 100%">
                      <el-option label="Father" value="Father"></el-option>
                      <el-option label="Mother" value="Mother"></el-option>
                      <el-option label="Brother" value="Brother"></el-option>
                      <el-option label="Sister" value="Sister"></el-option>
                      <el-option label="Wife" value="Wife"></el-option>
                      <el-option label="Son" value="Son"></el-option>
                      <el-option label="Daughter" value="Daughter"></el-option>
                    </el-select>
                  </el-form-item>
                </el-col>
                <el-col :span="12">                   
                  <el-form-item label="Nominee DOB" prop="nominee_dob">
                    <br>
                    <el-date-picker
                      v-model="temp.kyc.nominee_dob"
                      type="date"
                      format="yyyy-MM-dd"
                      value-format="yyyy-MM-dd"
                      placeholder="Date of birth">
                    </el-date-picker>
                  </el-form-item>                    
                  </el-form-item>
                  <el-form-item label="Nominee Contact" prop="nominee_contact">
                    <el-input v-model="temp.kyc.nominee_contact" />
                  </el-form-item>
                </el-col>
              </el-row>
              <el-form-item>
                <el-button type="primary"  icon="el-icon-finished" :loading="buttonLoading" :disabled="temp.kyc.verification_status=='submitted'" @click="onSubmit">
                  Update
                </el-button>
              </el-form-item>
            </el-tab-pane>
            <el-tab-pane v-loading="updating" label="Profile Image and KYC Images" name="kyc-images">
              <el-row :gutter="20">                
                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                                
                  <div class="img-upload">
                    <el-form-item  prop="profile_picture">
                      <label for="Profile Picture">Profile Picture</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleProfileChange"
                        :on-remove="handleProfileRemove"
                        :limit="1"
                        :file-list="profilefileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">
                        
                        <img v-if="temp.profile_picture" :src="temp.profile_picture?temp.profile_picture:''" class="avatar">
                        <i v-if="temp.profile_picture"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload>
                      <a  v-if="temp.profile_picture" :href="temp.profile_picture?temp.profile_picture:''" target="_blank">View full image.</a>                      
                    </el-form-item>
                  </div>
                </el-col>
                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                                
                  <div class="img-upload">
                    <el-form-item  prop="adhar_image">
                      <label for="Adhar Front Image">Adhar Front Image</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleAdharChange"
                        :on-remove="handleAdharRemove"
                        :limit="1"
                        :file-list="adharfileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">
                        
                        <img v-if="temp.kyc.adhar_image" :src="temp.kyc?temp.kyc.adhar_image:''" class="avatar">
                        <i v-if="temp.kyc.adhar_image"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload>
                      <a  v-if="temp.kyc.adhar_image" :href="temp.kyc?temp.kyc.adhar_image:''" target="_blank">View full image.</a>                      
                    </el-form-item>
                  </div>                 
                </el-col>
                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                                   
                  <div class="img-upload">
                    <el-form-item  prop="adhar_image_back">
                      <label for="Adhar Back Image">Adhar Back Image</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleAdharBackChange"
                        :on-remove="handleAdharBackRemove"
                        :limit="1"
                        :file-list="adharBackfileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">
                        
                        <img v-if="temp.kyc.adhar_image_back" :src="temp.kyc?temp.kyc.adhar_image_back:''" class="avatar">
                        <i v-if="temp.kyc.adhar_image_back"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload>
                      <a  v-if="temp.kyc.adhar_image_back" :href="temp.kyc?temp.kyc.adhar_image_back:''" target="_blank">View full image.</a>                      
                    </el-form-item>
                  </div>                 
                </el-col>
              </el-row>
              <el-row :gutter="20">

                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                  <div class="img-upload">
                    <el-form-item  prop="pan_image">
                      <label for="Pan Image">Pan Image</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handlePanChange"
                        :on-remove="handlePanRemove"
                        :limit="1"
                        :file-list="panfileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">
                        
                        <img v-if="temp.kyc.pan_image" :src="temp.kyc?temp.kyc.pan_image:''" class="avatar">
                        <i v-if="temp.kyc.pan_image"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload> 
                      <a  v-if="temp.kyc.pan_image" :href="temp.kyc?temp.kyc.pan_image:''" target="_blank">View full image.</a>                       
                    </el-form-item>
                  </div>
                </el-col>

                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                  <div class="img-upload">
                    <el-form-item  prop="pan_image">
                      <label for="Bank/Cheque Image Image">Bank Details</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleChequeChange"
                        :on-remove="handleChequeRemove"
                        :limit="1"
                        :file-list="chequefileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">
                        
                        <img v-if="temp.kyc.cheque_image" :src="temp.kyc?temp.kyc.cheque_image:''" class="avatar">
                        <i v-if="temp.kyc.cheque_image"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload> 
                      <a  v-if="temp.kyc.cheque_image" :href="temp.kyc?temp.kyc.cheque_image:''" target="_blank">View full image.</a>                     
                    </el-form-item>
                  </div>
                </el-col>

                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                  <div class="img-upload">
                    <el-form-item  prop="pan_image">
                      <label for="Distributor Contract">Distributor Contract</label>
                      <el-upload
                        class="avatar-uploader"
                        action="#"
                         ref="upload"
                        :show-file-list="true"
                        :auto-upload="false"
                        :on-change="handleDistributerChange"
                        :on-remove="handleDistributerRemove"
                        :limit="1"
                        :file-list="distributerfileList"
                        :on-exceed="handleExceed"
                        accept="image/png, image/jpeg">
                        
                        <img v-if="temp.kyc.distributor_image" :src="temp.kyc?temp.kyc.distributor_image:''" class="avatar">
                        <i v-if="temp.kyc.distributor_image"  slot="default" class="el-icon-plus"></i>
                        <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                      </el-upload> 
                      <a  v-if="temp.kyc.distributor_image" :href="temp.kyc?temp.kyc.distributor_image:''" target="_blank">View full image.</a>                     
                    </el-form-item>
                  </div>
                </el-col>

              </el-row>
              <el-form-item style="margin-top:20px;">
                <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" :disabled="temp.kyc.verification_status=='submitted'" @click="onSubmit">
                  Update
                </el-button>
                 <el-button type="success"  icon="el-icon-finished" :loading="buttonLoading" :disabled="temp.kyc.verification_status=='submitted'" @click="submitVerification">
                  {{temp.kyc.verification_status=='submitted'?'Submitted for apporval': 'Submit for verification'}}
                </el-button>
              </el-form-item>
            </el-tab-pane>
          </el-tabs>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>

<script>
import { updateProfile,getProfile } from "@/api/user/members";
import PanThumb from '@/components/PanThumb';
import { parseTime } from "@/utils";
import defaultSettings from '@/settings';
const { baseUrl } = defaultSettings;
//const userResource = new Resource('users');
export default {
  name: 'Profile',
  components: { PanThumb },
  data() {
    return {
      activeActivity: 'details',
      updating: false,
      buttonLoading:false,
      referral_link:'',
      adharfileList:[],
      adharfile:undefined,
      adharBackfileList:[],
      adharBackfile:undefined,
      profilefileList:[],
      profilefile:undefined,
      panfileList:[],
      panfile:undefined,
      chequefileList:[],
      distributerfileList:[],
      chequefile:undefined,
      distributerFile:undefined,
      temp:{
          id: undefined,
          name: undefined,
          username: undefined,
          email: undefined,
          password:undefined,
          contact: undefined,
          gender: "m",
          kyc:{
            address:undefined,
            pincode:undefined,
            adhar:undefined,
            adhar_image:undefined,
            pan:undefined,
            pan_image:undefined,
            pan_cheque:undefined,
            city:undefined,
            state:undefined,
            bank_ac_name:undefined,
            bank_name:undefined,
            bank_ac_no:undefined,
            ifsc:undefined,            
            nominee_name:undefined,
            nominee_relation:undefined,
            nominee_dob:undefined,
            nominee_contact:undefined,
          },
          profile_picture:undefined,
          parent:undefined,
          comission_from_self:0,
          comission_from_child:0,
          dob: undefined,
          is_active: 0,
      },
    };
  },
  created() {
    getProfile().then(response => {
        this.temp = response.data;
        if(!this.temp.kyc){
          this.temp.kyc={
            address:undefined,
            pincode:undefined,
            adhar:undefined,
            pan:undefined,
            city:undefined,
            state:undefined,
            bank_ac_name:undefined,
            bank_name:undefined,
            bank_ac_no:undefined,
            ifsc:undefined,
            nominee_name:undefined,
            nominee_relation:undefined,
            nominee_dob:undefined,
            nominee_contact:undefined,
          }
        }
        this.referral_link=baseUrl+'#/register?sponsor_code='+this.temp.username;
    });
  },
  methods: {
    handleAdharChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.adharfile=f.raw      
    },
    handleAdharRemove(file, fileList) {
       this.adharfile=undefined;
       this.adharfileList=[];
    },
    handleAdharBackChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.adharBackfile=f.raw      
    },
    handleAdharBackRemove(file, fileList) {
       this.adharBackfile=undefined;
       this.adharBackfileList=[];
    },
    handleProfileRemove(file, fileList) {
       this.profilefile=undefined;
       this.profilefileList=[];
    },
    handleProfileChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.profilefile=f.raw      
    },    
    handlePanChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.panfile=f.raw      
    },
    handlePanRemove(file, fileList) {
       this.panfile=undefined;
       this.panfileList=[];
    },
    handlePanChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.panfile=f.raw      
    },
     handleChequeChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.chequefile=f.raw      
    },
     handleDistributerChange(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.distributerFile=f.raw      
    },
     distributor_image(f, fl){     
      if(fl.length > 1){
        fl.shift()  
      }      
      this.distributerFile=f.raw      
    },
    handleChequeRemove(file, fileList) {
       this.chequefile=undefined;
       this.chequefileList=[];
    },
    handleDistributerRemove(file, fileList) {
       this.distributerFile=undefined;
       this.distributerfileList=[];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    onCopy(){
      this.$notify({
          title: "Copied",
          message: "Referral link copied successfully.",
          type: "success",
          duration: 2000
        })
 
    },
    submitVerification(){
      this.temp.kyc.verification_status='submitted';
      this.onSubmit();
    },
    onSubmit() {
      this.updating = true;
      this.buttonLoading=true;

      var form = new FormData();
      let form_data=this.temp;

      for ( var key in form_data ) {
        if(form_data[key] !== undefined && form_data[key] !== null){
          if(key=='kyc'){
            form.append(key, JSON.stringify(form_data[key]));  
          }else{
            form.append(key, form_data[key]);  
          }
          
        }
      }

      form.append('adhar_image', this.adharfile);
      form.append('adhar_image_back', this.adharBackfile);
      form.append('profile_picture', this.profilefile);
      form.append('pan_image', this.panfile);
      form.append('cheque_image', this.chequefile);
      form.append('distributor_image', this.distributerFile);

      updateProfile(form).then((response) => {
        this.updating = false;
        this.buttonLoading=false;
        this.temp=response.data;
        this.$notify({
          title: "Success",
          message: "Profile updated Successfully",
          type: "success",
          duration: 2000
        })
        this.adharfile=undefined
        this.adharfileList=[];
        this.adharBackfile=undefined
        this.adharBackfileList=[];
        this.profilefile=undefined
        this.profilefileList=[];
        this.panfile=undefined
        this.panfileList=[];
        this.chequefile=undefined
        this.distributerFile=undefined
        this.chequefileList=[];
        this.distributerfileList=[];
      }).catch((err)=>{
        this.updating = false;
        this.buttonLoading=false;
      });
    },
  },
};
</script>

<style lang="scss" scoped>
.user-activity {
  .user-block {
    .username, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }
    img {
      width: 40px;
      height: 40px;
      float: left;
    }
    :after {
      clear: both;
    }
    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }
    span {
      font-weight: 500;
      font-size: 12px;
    }
  }
  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;
    .image {
      width: 100%;
    }
    .user-images {
      padding-top: 20px;
    }
  }
  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;
    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }
    .link-black {
      &:hover, &:focus {
        color: #999;
      }
    }
  }
  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}

@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}

.avatar {
    width: 200px;
    height: 115px;
    display: block;
  }

  .avatar-uploader-icon {
    font-size: 28px;
    color: #8c939d;
    width: 200px;
    height: 115px !important;
    line-height: 115px;
    text-align: center;
  }

.user-profile {
  .user-name {
    font-weight: bold;
  }
  .box-center {
    padding-top: 10px;
  }
  .user-role {
    padding-top: 10px;
    font-weight: 400;
    font-size: 14px;
  }
  .box-social {
    padding-top: 30px;
    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }
  .user-follow {
    padding-top: 20px;
  }
}
</style>
