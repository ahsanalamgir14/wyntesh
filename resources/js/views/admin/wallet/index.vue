<template>
  <div class="app-container">    
    </el-row>
    <div class="filter-container">
       <el-input
        v-model="listQuery.search"
        placeholder="Search Records"
        style="width: 200px;"
        class="filter-item"
        @keyup.enter.native="handleFilter"
      />

      <el-select v-model="listQuery.status" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Status">
        <el-option
          v-for="item in statuses"
          :key="item.name"
          :label="item.name"
          :value="item.name">
        </el-option>
      </el-select>

      <el-date-picker
        v-model="dateRangeFilter"
        class="filter-item"
        type="daterange"
        align="right"
        unlink-panels
        @change="handleFilter"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        range-separator="|"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions">
      </el-date-picker>
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
      <el-table-column label="Actions" align="center" width="230px" class-name="small-padding">        
        <template slot-scope="{row}">
          <el-tooltip content="Approve" placement="right" effect="dark" v-if="row.request_status=='Pending'">
            <el-button
              circle
              type="success"
              icon="el-icon-check"
              :loading="buttonLoading"
              @click="handleApprove(row)"
              ></el-button>
          </el-tooltip>

          <el-tooltip content="Reject" placement="right" effect="dark" v-if="row.request_status=='Pending'">
            <el-button
              circle
              type="warning"
              icon="el-icon-close"
              :loading="buttonLoading"
              @click="rejectRequest(row)"
              ></el-button>
          </el-tooltip>
          
          <el-tooltip content="Delete" placement="right" effect="dark" v-if="row.request_status=='Pending'">
            <el-button
              circle
              type="danger"
              icon="el-icon-delete"
              :loading="buttonLoading"
              @click="deleteRequest(row)"
              ></el-button>
          </el-tooltip>

          <el-tooltip content="View Kyc" placement="right" effect="dark" >
            <el-button
              circle
              type="primary"
              icon="el-icon-view"
              @click="viewKyc(row)"
              ></el-button>
          </el-tooltip>
        </template>
      </el-table-column> 
      <el-table-column label="Beneficiary Name" width="200px" align="right">
        <template slot-scope="{row}">
          <span class="link-type" @click="viewKyc(row)">{{ row.member.kyc.bank_ac_name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Bank Name" width="200px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.member.kyc.bank_name }} </span>
        </template>
      </el-table-column>

      <el-table-column label="Bank Account Number" class-name="status-col" width="200px">
        <template slot-scope="{row}">
          <span>{{ row.member.kyc.bank_ac_no }}</span>
          
        </template>

      </el-table-column>
      <el-table-column label="IFSC Code" class-name="status-col" width="200px">
        <template slot-scope="{row}">
          <span>{{ row.member.kyc.ifsc }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" class-name="status-col" width="200px">
        <template slot-scope="{row}">
            {{ row.amount }}
        </template>
      </el-table-column>
      <el-table-column label="Created At" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
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

    <el-dialog title="Approve Withdrawal" width="60%" top="30px" :visible.sync="dialogApproveVisible">
      <el-form ref="formApprove" :rules="rules" :model="temp">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Amount to withdraw" prop="amount" label-width="120">
              <el-input  type="number" disabled min=1 @change="handleDebitChange" v-model="temp.amount" ></el-input>
            </el-form-item>
            <el-form-item label="TDS (%)" label-width="120" prop="tds_amount">
              <el-input  type="number"  @change="handleDebitChange" min=0 v-model="temp.tds_percentage" ></el-input>
            </el-form-item>
            <el-form-item label="TDS Amount" disabled label-width="120" prop="tds_amount">
              <el-input  type="number"  min=0 v-model="temp.tds_amount" ></el-input>
            </el-form-item>
            <el-form-item label="Final Amount" label-width="120" prop="final_amount">
              <el-input  type="number"  min=1 v-model="temp.final_amount" ></el-input>
            </el-form-item>
            
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item prop="payment_made_at" class="dob-input" label="Payment made at">
              </br>
              <el-date-picker
                  style="width: 100%"
                  :picker-options="datePickerOptions"
                  v-model="temp.payment_made_at"
                  type="date"
                  format="yyyy-MM-dd"
                  value-format="yyyy-MM-dd"
                  placeholder="Payment made at">
                </el-date-picker>
            </el-form-item>

            

            <el-form-item label="Payment Status" prop="payment_status">
              <el-select v-model="temp.payment_status"  style="width:100%;" filterable placeholder="Select Status">
                <el-option
                  v-for="item in payment_statuses"
                  :key="item.name"
                  :label="item.name"
                  :value="item.name">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="temp.note"
                :rows="2"
                placeholder="Please Enter note">
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogApproveVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="approveRequest()">Approve</el-button>
      </span>
    </el-dialog>

    <el-dialog title="KYC" width="85%" top="30px"  :visible.sync="dialogKycVisible">
      <el-form  :model="tempKyc">
        <el-tabs v-model="kycsTabs" type="border-card" >
          <el-tab-pane label="Kyc and Bank" name="kyc">
            <el-row :gutter="20">
              <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >            
                
                <el-form-item label="Adhar" prop="adhar">
                  <el-input disabled max="16" v-model="tempKyc.adhar" />
                </el-form-item>
               
                <el-form-item label="City" prop="city">
                  <el-input  disabled v-model="tempKyc.city" />
                </el-form-item>
                <el-form-item label="State" prop="state">
                  <el-input disabled v-model="tempKyc.state" />
                </el-form-item>
                <el-form-item label="Address" prop="address">
                  <el-input type="textarea" disabled
                    :rows="2"
                    placeholder="Address" v-model="tempKyc.address" />
                </el-form-item>
                <el-form-item label="Pincode" prop="pincode">
                  <el-input disabled v-model="tempKyc.pincode" />
                </el-form-item>

              </el-col>
              <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                 <el-form-item label="Pan" prop="pan">
                  <el-input max="10" disabled v-model="tempKyc.pan" />
                </el-form-item>
                <el-form-item label="Bank A/C Name" prop="bank_ac_name">
                  <el-input disabled v-model="tempKyc.bank_ac_name"  />
                </el-form-item>
                <el-form-item label="Bank Name" prop="bank_name">
                  <el-input disabled v-model="tempKyc.bank_name"  />
                </el-form-item>
                <el-form-item label="A/C No" prop="bank_ac_no">
                  <el-input disabled v-model="tempKyc.bank_ac_no"  />
                </el-form-item>
                <el-form-item label="IFSC Code" prop="ifsc">
                  <el-input disabled v-model="tempKyc.ifsc"  />
                </el-form-item>
              </el-col>
            </el-row>
            
          </el-tab-pane>
          <el-tab-pane  label="KYC Images" name="kyc-images">
            <el-row :gutter="20">
              <el-col :span="8">            
                
                <div class="img-upload">
                  <el-form-item  prop="adhar_image">
                    <label for="Adhar Image">Adhar Image</label>
                    <el-upload disabled
                      class="avatar-uploader"
                      action="#"
                       ref="upload"
                      :show-file-list="true"
                      :auto-upload="false"
                      
                      accept="image/png, image/jpeg">
                      
                      <img v-if="tempKyc.adhar_image" :src="tempKyc?tempKyc.adhar_image:''" class="avatar">
                      <i v-if="tempKyc.adhar_image"  slot="default" class="el-icon-plus"></i>
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload>
                    <a  v-if="tempKyc.adhar_image" :href="tempKyc?tempKyc.adhar_image:''" target="_blank">View full image.</a>                      
                  </el-form-item>
                </div>
               
              </el-col>
              <el-col :span="8">
                <div class="img-upload">
                  <el-form-item  prop="pan_image">
                    <label for="Pan Image">Pan Image</label>
                    <el-upload disabled
                      class="avatar-uploader"
                      action="#"
                       ref="upload"
                      :show-file-list="true"
                      :auto-upload="false"
                      
                      accept="image/png, image/jpeg">
                      
                      <img v-if="tempKyc.pan_image" :src="tempKyc?tempKyc.pan_image:''" class="avatar">
                      <i v-if="tempKyc.pan_image"  slot="default" class="el-icon-plus"></i>
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload> 
                    <a  v-if="tempKyc.pan_image" :href="tempKyc?tempKyc.pan_image:''" target="_blank">View full image.</a>                       
                  </el-form-item>
                </div>
              </el-col>
              <el-col :span="8">
                <div class="img-upload">
                  <el-form-item  prop="pan_image">
                    <label for="Bank/Cheque Image Image">Bank/Cheque Image</label>
                    <el-upload disabled
                      class="avatar-uploader"
                      action="#"
                       ref="upload"
                      :show-file-list="true"
                      :auto-upload="false"
                      
                      accept="image/png, image/jpeg">
                      
                      <img v-if="tempKyc.cheque_image" :src="tempKyc?tempKyc.cheque_image:''" class="avatar">
                      <i v-if="tempKyc.cheque_image"  slot="default" class="el-icon-plus"></i>
                      <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                    </el-upload> 
                    <a  v-if="tempKyc.cheque_image" :href="tempKyc?tempKyc.cheque_image:''" target="_blank">View full image.</a>                     
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
      </div>
    </el-dialog>

  </div>
</template>

<script>
import {
  fetchWithdrawalRequests,
  deleteWithdrawalRequest,
  approveWithdrawalRequest,
  rejectWithdrawalRequest
} from "@/api/admin/wallet";
import { getCompanySettings } from "@/api/admin/company-settings";
import { getStatuesAll } from "@/api/admin/config";
import CountTo from 'vue-count-to'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "withdrawal-requests",
  components: { Pagination,CountTo },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        Approved: "success",
        Pending: "info",
        Rejected: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      kycsTabs:'kyc',
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        status:'Pending',
        sort: "+id",
        date_range:''
      },
      balance:60000,
      dateRangeFilter:'',
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id: undefined,
        amount:0,
        tds_amount:0,
        tds_percentage:0,
        final_amount:0,
        payment_made_at:undefined,
        payment_status:'Paid',
        note:undefined,
      },
      tempKyc: {
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
      datePickerOptions: {
        disabledDate(time) {
          return time.getTime() > Date.now();
        }
      },
      dialogApproveVisible:false,
      dialogKycVisible:false,
      dialogStatus: "",
      statuses:[],
      payment_statuses:[],
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        amount: [
          { type:"number", required: true, message: "Enter amount", trigger: "blur" }
        ],
        final_amount: [
          { type:"number", required: true, message: "Final Amount cannot be zero", trigger: "blur" }
        ],
      },
      settings:undefined,
      downloadLoading: false,
      buttonLoading: false,
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
    };
  },
  created() {
    this.getList();
    this.getCompanySettings();
  },
  methods: {
    getList() {
      this.listLoading = true;     
      fetchWithdrawalRequests(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.balance = parseFloat(response.balance);
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },
    getCompanySettings() {      
      getCompanySettings().then(response => {
        this.settings = response.data        
        //this.temp.tds_percentage=this.settings.tds_percentage;
      });
      getStatuesAll('credit_requests').then(response => {
        this.statuses = response.data;
      });
      getStatuesAll('withdrawals').then(response => {
        this.payment_statuses = response.data;
      });
    },
    handleDebitChange(){
      let tds=(this.temp.amount*this.temp.tds_percentage)/100
      this.temp.tds_amount=parseFloat(tds);
      this.temp.amount=parseFloat(this.temp.amount);
      this.temp.final_amount=(parseFloat(this.temp.amount, 10)-parseFloat(this.temp.tds_amount, 10));
    },
    rejectRequest(row){
      this.$prompt('Enter note', 'Confirm Rejection', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
        }).then(({ value }) => {

          let data={
            id:row.id,
            note:value
          };
          this.buttonLoading=true;
          rejectWithdrawalRequest(data).then((res) => {          
            this.$notify({
                title: "Success",
                message: res.message,
                type: "success",
                duration: 2000
            });
            this.buttonLoading=false;
            const index = this.list.indexOf(row);
            this.list.splice(index, 1);
          }).catch((err)=>{
            this.buttonLoading=false;
          });
      })
    },
    deleteRequest(row){
      this.$confirm('Are you sure you want to delete withdrawal Request?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.buttonLoading=true;
        deleteWithdrawalRequest(row.id).then((data) => {
          this.buttonLoading=false;
          this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
          });
          const index = this.list.indexOf(row);
          this.list.splice(index, 1);
        }).catch((err)=>{
          this.buttonLoading=false;
        });
      })
    },
    handleApprove(row){
      let temp = Object.assign({}, row);
      let tds=(temp.amount*this.temp.tds_percentage)/100;
      this.temp.id=temp.id;
      this.temp.tds_amount=parseFloat(tds);
      this.temp.amount=parseFloat(temp.amount);
      this.temp.final_amount=(parseFloat(temp.amount, 10)-parseFloat(this.temp.tds_amount, 10));
      this.dialogApproveVisible=true;
    },
    approveRequest(){
      this.$refs["formApprove"].validate(valid => {
        if(parseFloat(this.temp.final_amount) <=0 ){
          this.$message.error('Withdrawal amount cannot be 0.');
          return;
        }
       
        if (valid) {
           this.buttonLoading=true;
          approveWithdrawalRequest(this.temp).then((response) => {
            this.getList();
            this.resetTemp();
            this.buttonLoading=false;
            this.dialogApproveVisible = false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })

          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    viewKyc(row) {  
      this.kycsTabs='kyc';
      let tempKyc = Object.assign({}, row);
      this.tempKyc = tempKyc.member.kyc;
      this.dialogKycVisible = true;
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.listQuery.date_range=this.dateRangeFilter;
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
      this.temp = {
        id: undefined,
        amount:0,
        tds_percentage:0,
        tds_amount:0,
        final_amount:0,
        payment_made_at:undefined,
        payment_status:'Paid',
        note:undefined,
      };
      this.temp.tds_percentage=this.settings.tds_percentage;
    },
    clean(obj) {
      for (var propName in obj) { 
        if (obj[propName] === null || obj[propName] === undefined) {
          delete obj[propName];
        }
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Beneficiary Name",
          "Bank Name",
          "Bank Account Number",
          "IFSC Code",
          "Amount",
        ];
        const filterVal = [
          "id",
          "beni",
          "bank",
          "banckacc",
          "ifsc",
          "amt",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "withdrawal-requests"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j=='beni'){
            return v.member.kyc.bank_ac_name
          } else if(j=='bank'){
            return v.member.kyc.bank_name
          } else if(j=='banckacc'){
            return v.member.kyc.bank_ac_no
          } else if(j=='ifsc'){
            return v.member.kyc.ifsc
          } else if(j=='amt'){
            return v.amount
          }else {
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

<style lang="scss" scoped>
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}


</style>
