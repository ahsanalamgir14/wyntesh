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
      this.temp.description=this.temp.description.replace(/@name(\S*)/g, '<b>'+this.user_details.name+'</b>.');
      this.temp.description=this.temp.description.replace(/@company(\S*)/g, '<b>'+this.company_details.company_name+'.</b>')
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
