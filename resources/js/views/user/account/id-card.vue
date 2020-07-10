<template>
  <div class="app-container">
    <el-row justify="center" style="margin-left: 50px;margin-right: 50px;">
      <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
        <div class="id-card-tag"></div>
          <div class="id-card-tag-strip"></div>
          <div class="id-card-hook"></div>
          <div class="id-card-holder">
            <div class="id-card">
              <div class="header">
                <img src="@/assets/images/dark_logo.png" >
              </div>
              <h2>{{company_details.company_name}}</h2>
              <div class="photo">
                <div v-if="user_details.profile_picture!='' ">
                    <img :src="user_details.profile_picture">
                </div>
                <div v-else>
                    <img src="@/assets/images/avatar.png">
                </div>

              </div>
              <h2>{{user_details.name}}</h2>
              <h2>{{user_details.username}}</h2>
              <h3>{{company_details.website}}</h3>
              <hr>
              <p><strong>{{company_details.company_name}}</strong>{{company_details.address}},{{company_details.city}} <p>
              <p>{{company_details.state}},{{company_details.country}} <strong>{{company_details.pincode}}</strong></p>
              <p>Ph: {{company_details.contact_phone}} | E-ail: {{company_details.contact_email}} </p>

            </div>
          </div>
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
        this.user_details=response.user;
        this.company_details=response.company_details;
      if(response.data){
        this.temp = response.data; 
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

.id-card-holder {
      width: 225px;
        padding: 4px;
        margin: 0 auto;
        background-color: #1f1f1f;
        border-radius: 5px;
        position: relative;
    }
    .id-card-holder:after {
        content: '';
        width: 7px;
        display: block;
        background-color: #0a0a0a;
        height: 100px;
        position: absolute;
        top: 105px;
        border-radius: 0 5px 5px 0;
    }
    .id-card-holder:before {
        content: '';
        width: 7px;
        display: block;
        background-color: #0a0a0a;
        height: 100px;
        position: absolute;
        top: 105px;
        left: 222px;
        border-radius: 5px 0 0 5px;
    }
    .id-card {
      
      background-color: #fff;
      padding: 10px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 0 1.5px 0px #b9b9b9;
    }
    .id-card img {
      margin: 0 auto;
    }
    .header img {
      width: 87px;
        margin-top: 15px;
    }
    .photo img {
      width: 80px;
        margin-top: 15px;
        border-radius: 50px;
    }
    h2 {
      font-size: 15px;
      margin: 5px 0;
    }
    h3 {
      font-size: 12px;
      margin: 2.5px 0;
      font-weight: 300;
    }
    .qr-code img {
      width: 50px;
    }
    p {
      font-size: 5px;
      margin: 2px;
    }
    .id-card-hook {
      background-color: #000;
        width: 70px;
        margin: 0 auto;
        height: 15px;
        border-radius: 5px 5px 0 0;
    }
    .id-card-hook:after {
      content: '';
        background-color: #d7d6d3;
        width: 47px;
        height: 6px;
        display: block;
        margin: 0px auto;
        position: relative;
        top: 6px;
        border-radius: 4px;
    }
    .id-card-tag-strip {
      width: 45px;
        height: 40px;
        background-color: #0950ef;
        margin: 0 auto;
        border-radius: 5px;
        position: relative;
        top: 9px;
        z-index: 1;
        border: 1px solid #0041ad;
    }
    .id-card-tag-strip:after {
      content: '';
        display: block;
        width: 100%;
        height: 1px;
        background-color: #c1c1c1;
        position: relative;
        top: 10px;
    }
    .id-card-tag {
      width: 0;
      height: 0;
      border-left: 100px solid transparent;
      border-right: 100px solid transparent;
      border-top: 100px solid #0958db;
      margin: -10px auto -30px auto;
    }
    .id-card-tag:after {
      content: '';
        display: block;
        width: 0;
        height: 0;
        border-left: 50px solid transparent;
        border-right: 50px solid transparent;
        border-top: 100px solid #d7d6d3;
        margin: -10px auto -30px auto;
        position: relative;
        top: -130px;
        left: -50px;
    }

</style>
