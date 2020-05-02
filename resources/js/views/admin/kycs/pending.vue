<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>
    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="80" class-name="small-padding">
        <template slot-scope="{row}">          
          <el-button
              @click="handleEdit(row)"
              
              circle
              type="success"
          ><i class="fas fa-eye"></i></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Member ID" width="150px">
        <template slot-scope="{row}">
          <span class="link-type" @click="handleEdit(row)">{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="150px">
        <template slot-scope="{row}">
          <span>{{ row.member.user.name }}</span>
        </template>
      </el-table-column>  
      <el-table-column label="PAN Card" width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.pan }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Adhar Card" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.adhar }}</span>
        </template>
      </el-table-column>
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />

    <el-dialog :title="textMap[dialogStatus]" width="85%" top="30px"  :visible.sync="dialogKycVisible">
      <el-form  :model="temp">
        <el-tabs v-model="kycsTabs" type="border-card" >
          <el-tab-pane v-loading="updating" label="Kyc and Bank" name="kyc">
            <el-row :gutter="20">
              <el-col :span="12">            
                
                <el-form-item label="Adhar" prop="adhar">
                  <el-input max="16" v-model="temp.adhar" />
                </el-form-item>
               
                <el-form-item label="City" prop="city">
                  <el-input  v-model="temp.city" />
                </el-form-item>
                <el-form-item label="State" prop="state">
                  <el-input v-model="temp.state" />
                </el-form-item>
                <el-form-item label="Address" prop="address">
                  <el-input type="textarea"
                    :rows="2"
                    placeholder="Address" v-model="temp.address" />
                </el-form-item>
                <el-form-item label="Pincode" prop="pincode">
                  <el-input v-model="temp.pincode" />
                </el-form-item>

              </el-col>
              <el-col :span="12">
                 <el-form-item label="Pan" prop="pan">
                  <el-input max="10" v-model="temp.pan" />
                </el-form-item>
                <el-form-item label="Bank A/C Name" prop="bank_ac_name">
                  <el-input v-model="temp.bank_ac_name"  />
                </el-form-item>
                <el-form-item label="Bank Name" prop="bank_name">
                  <el-input v-model="temp.bank_name"  />
                </el-form-item>
                <el-form-item label="A/C No" prop="bank_ac_no">
                  <el-input v-model="temp.bank_ac_no"  />
                </el-form-item>
                <el-form-item label="IFSC Code" prop="ifsc">
                  <el-input v-model="temp.ifsc"  />
                </el-form-item>
              </el-col>
            </el-row>
            
          </el-tab-pane>
          <el-tab-pane v-loading="updating" label="KYC Images" name="kyc-images">
            <el-row :gutter="20">
              <el-col :span="8">            
                
                <div class="img-upload">
                  <el-form-item  prop="adhar_image">
                    <label for="Adhar Image">Adhar Image</label>
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
                      
                      <img v-if="temp.adhar_image" :src="temp?temp.adhar_image:''" class="avatar">
                      <i v-if="temp.adhar_image"  slot="default" class="el-icon-plus"></i>
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                    <a  v-if="temp.adhar_image" :href="temp?temp.adhar_image:''" target="_blank">View full image.</a>                      
                  </el-form-item>
                </div>
               
              </el-col>
              <el-col :span="8">
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
                      
                      <img v-if="temp.pan_image" :src="temp?temp.pan_image:''" class="avatar">
                      <i v-if="temp.pan_image"  slot="default" class="el-icon-plus"></i>
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload> 
                    <a  v-if="temp.pan_image" :href="temp?temp.pan_image:''" target="_blank">View full image.</a>                       
                  </el-form-item>
                </div>
              </el-col>
              <el-col :span="8">
                <div class="img-upload">
                  <el-form-item  prop="pan_image">
                    <label for="Bank/Cheque Image Image">Bank/Cheque Image</label>
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
                      
                      <img v-if="temp.cheque_image" :src="temp?temp.cheque_image:''" class="avatar">
                      <i v-if="temp.cheque_image"  slot="default" class="el-icon-plus"></i>
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload> 
                    <a  v-if="temp.cheque_image" :href="temp?temp.cheque_image:''" target="_blank">View full image.</a>                     
                  </el-form-item>
                </div>
              </el-col>
            </el-row>
            
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogKycVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" :loading="buttonLoading" @click="onSubmit()">
          Update Kyc Details
        </el-button>
        <el-button type="success" :loading="buttonLoading" @click="verifyKyc()">
          Approve
        </el-button>
        <el-button type="danger" :loading="buttonLoading" @click="rejectKyc()">
          Reject
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchPendingList, updateKyc } from "@/api/admin/kycs";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import Tinymce from '@/components/Tinymce'

export default {
  name: "ComplexTable",
  components: { Pagination,Tinymce },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      kycsTabs:'kyc',
      updating:false,
      tableKey: 0,
      list: [],
      total: 5,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search:'',
        sort: "+id"
      },
      adharfileList:[],
      adharfile:undefined,
      panfileList:[],
      panfile:undefined,
      chequefileList:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
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
        member:{
          user:{
            name:undefined,
            username:undefined,
            contact:undefined,
            email:undefined,
            gender:undefined,
            dob:undefined,
          }
        }
      },

      dialogKycVisible:false,
      dialogStatus: "",
      textMap: {
        update: "View KYC",
        create: "Create"
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
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
    handleChequeRemove(file, fileList) {
       this.chequefile=undefined;
       this.chequefileList=[];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    getList() {
      this.listLoading = true;
      fetchPendingList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    resetTemp() {
      this.temp ={
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
        verification_status:undefined,
        member:{
          user:{
            name:undefined,
            username:undefined,
            contact:undefined,
            email:undefined,
            gender:undefined,
            dob:undefined,
          }
        }
      };

      this.adharfile=undefined
      this.adharfileList=[];
      this.panfile=undefined
      this.panfileList=[];
      this.chequefile=undefined
      this.chequefileList=[];
    },
    verifyKyc(){
      this.temp.verification_status='verified';
      this.onSubmit();
    },
    rejectKyc(){
      this.temp.verification_status='rejected';
      this.onSubmit();
    },
    handleEdit(row) {
     
      this.temp = Object.assign({}, row);
      this.dialogStatus = "update";
      this.dialogKycVisible = true;
    },
    onSubmit() {
      this.updating = true;
      
      var form = new FormData();
      let form_data=this.temp;

      for ( var key in form_data ) {
        if(form_data[key] !== undefined && form_data[key] !== null){         
            form.append(key, form_data[key]);            
        }
      }

      form.append('adhar_image', this.adharfile);
      form.append('pan_image', this.panfile);
      form.append('cheque_image', this.chequefile);

      updateKyc(form).then((response) => {
        this.updating = false;
        this.temp=response.data;
        

        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })

        this.resetTemp();
        this.getList();

        this.dialogKycVisible = false;

        this.adharfile=undefined
        this.adharfileList=[];
        this.panfile=undefined
        this.panfileList=[];
        this.chequefile=undefined
        this.chequefileList=[];
      })
    },
   
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Name",
          "PAN",
          "Adhar"
        ];
        const filterVal = [
          "id",
          "name",
          "pan",
          "adhar",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "Kyc"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "name") {
            return v.member.user.name;
          }else if (j === "id") {
            return v.member.user.username;
          }else{
            return v[j];
          }
        })
      );
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
  }
};
</script>

<style scoped>
.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}

@media (min-width:750px) {
  .img-upload{
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
  .el-table  td, .el-table  th {
    padding: 2px 0;
  }
  .el-table--medium td, .el-table--medium th {
    padding: 1px 0;
}
</style>
