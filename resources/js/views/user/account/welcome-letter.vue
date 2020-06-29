<template>
  <div class="app-container">
    <el-row justify="center" style="margin-left: 50px;margin-right: 50px;">
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
        <span v-html="temp.description"></span>
      </el-col>
    </el-row>
    <!-- <el-row >
      <hr>
      <div style="float: right;margin-top:10px;">
        <el-button
          type="success"
          @click="print()"
        >Save</el-button>
      </div>
    </el-row> -->
  </div>
</template>

<script>
import {
  getWelcomeLetter
} from "@/api/user/welcome-letter";
import { parseTime } from "@/utils";

export default {
  name: "Settings",
  data() {
    return {
      temp: {
        description:undefined,
      },
      user_details:{},
      company_details:{},
      buttonLoading: false
    };
  },
  created() {
    getWelcomeLetter().then(response => {
      if(response.data){
        this.temp = response.data; 
        this.user_details=response.user;
        this.company_details=response.company_details;
        this.hashTags();
      }      
    });
  },
  methods: {
    print(){

    },
    hashTags: function() {
      if(this.user_details.profile_picture){
        this.temp.description=this.temp.description.replace(/@profile_picture(\S*)/g, '<img width="100px" style="border-radius:10px" src="'+this.user_details.profile_picture+'"</img>');
      }else{
        this.temp.description=this.temp.description.replace(/@profile_picture(\S*)/g, '');
      }
      this.temp.description=this.temp.description.replace(/@name(\S*)/g, '<b>'+this.user_details.name+'</b>');
      this.temp.description=this.temp.description.replace(/@username(\S*)/g, '<b>'+this.user_details.username+'</b>');
      this.temp.description=this.temp.description.replace(/@dob(\S*)/g, '<b>'+this.user_details.dob+'</b>');
      this.temp.description=this.temp.description.replace(/@contact(\S*)/g, '<b>'+this.user_details.contact+'</b>');
      this.temp.description=this.temp.description.replace(/@address(\S*)/g, '<b>'+this.user_details.address+'</b>');
      this.temp.description=this.temp.description.replace(/@joining_date(\S*)/g, '<b>'+parseTime(this.user_details.created_at,'{y}-{m}-{d}')+'</b>');
      this.temp.description=this.temp.description.replace(/@sponsor_id(\S*)/g, '<b>'+this.user_details.sponsor_id+'</b>');
      this.temp.description=this.temp.description.replace(/@sponsor_name(\S*)/g, '<b>'+this.user_details.sponsor_name+'</b>');
      this.temp.description=this.temp.description.replace(/@company(\S*)/g, '<b>'+this.company_details.company_name+'</b>')
    }
  }
};
</script>

<style scoped>

.edit-input {
  padding-right: 100px;
}

.el-form-item--medium .el-form-item__content, .el-form-item--medium .el-form-item__label {
    line-height: 0px;
}
</style>
