t<template>
  <div class="app-container">
    
    <el-row :gutter="20" v-if="checkRole(['superadmin'])">
      <el-col  :span="24">
        <div class="filter-container">    
          <el-button
            class="filter-item"
            style="margin-left: 10px;float: right;"
            type="primary"
            icon="el-icon-plus"
            @click="handleCreate"
          >Add</el-button>
        </div>
      </el-col>
    </el-row>
    <el-form ref="dataForm" label-position="top"  style="">
      <el-tabs type="border-card">
        <el-tab-pane label="Notification Settings">          
          <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="8" :xl="8" v-for="(setting,index) in temp" :key="setting.id">
              <el-form-item :label="setting.name" :prop="setting.alias">
                <el-checkbox v-model="temp[index].is_email" label="Email" border></el-checkbox>
                <el-checkbox v-model="temp[index].is_sms" label="SMS" border></el-checkbox>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row >
            <hr>
            <div style="float: right;margin-top:10px;">
              <el-button
                type="success"
                icon="el-icon-finished" :loading="buttonLoading"
                @click="handleSaveSettings"
              >Save</el-button>
            </div>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </el-form>

    <el-dialog :title="dialogTitle" width="40%" top="30px"  :visible.sync="dialogAddNotificationSetting">
      <el-form ref="addNotificationSettingForm" :rules="rules" :model="setting"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            
            <el-form-item label="Name" prop="name">
              <el-input  v-model="setting.name" />
            </el-form-item>
            <el-form-item label="Notification Option" >
              <br>
              <el-checkbox v-model="setting.is_email" label="Email" border></el-checkbox>
              <el-checkbox v-model="setting.is_sms" label="SMS" border></el-checkbox>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogAddNotificationSetting = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="createData()">
          Add
        </el-button>
      </div>
    </el-dialog>

  </div>
</template>

<script>
import {
  getNotificationSettings,
  saveNotificationSettings,
  createNotificationSettings
} from "@/api/admin/config";
import role from '@/directive/role'; 
import checkRole from '@/utils/role';

export default {
  name: "Settings",
  directives: { role },
  data() {
    return {
      temp: [{
        id:undefined,
        name:undefined,
        alias:undefined,
        is_email:false,
        is_sms:false
      }],
      setting:{
        name:undefined,
        alias:undefined,
        is_email:false,
        is_sms:false
      },
      rules: {
        name: [{  required: true, message: 'Name is required', trigger: 'blur' }],      
      },
      dialogTitle:'',
      buttonLoading: false,
      dialogAddNotificationSetting:false,
    };
  },
  created() {
    getNotificationSettings().then(response => {
      response.data.map((item)=>{
        item.is_email=item.is_email?true:false;
        item.is_sms=item.is_sms?true:false;
        return item;
      })
      this.temp = response.data;
    });
  },
  methods: {
    checkRole,
    handleCreate(){
      this.resetSetting();
      this.dialogTitle="Add Notification Setting";
      this.dialogAddNotificationSetting=true;
    },
    resetSetting(){
      this.setting={
        id:undefined,
        name:undefined,
        alias:undefined,
        is_email:false,
        is_sms:false
      };
    },
    createData() {
      
      this.$refs["addNotificationSettingForm"].validate(valid => {
        if (valid) {       
          this.buttonLoading=true;
          createNotificationSettings(this.setting).then((response) => {
            response.data.map((item)=>{
              item.is_email=item.is_email?true:false;
              item.is_sms=item.is_sms?true:false;
              return item;
            })
            this.temp = response.data;
            this.dialogAddNotificationSetting = false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetSetting();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleSaveSettings() {
        this.buttonLoading=true;
        saveNotificationSettings(this.temp).then((response) => {
          
          response.data.map((item)=>{
            item.is_email=item.is_email?true:false;
            item.is_sms=item.is_sms?true:false;
            return item;
          })
          this.temp=response.data;
          this.buttonLoading=false;
          this.$notify({
            title: "Success",
            message: response.message,
            type: "success",
            duration: 2000
          });
        }).catch((err)=>{
          this.buttonLoading=false;
        });
    },
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
