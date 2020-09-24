<template>
  <div class="app-container">
    
    <div class="filter-container">
       <el-input
          v-model="listQuery.transfered_from"
          placeholder="Transfer from"
          style="width: 200px;"
          class="filter-item"
          @keyup.enter.native="handleFilter"
        />

       <el-input
          v-model="listQuery.transfered_to"
          placeholder="Transfer to"
          style="width: 200px;"
          class="filter-item"
          @keyup.enter.native="handleFilter"
        />

      <el-date-picker
        v-model="listQuery.date_range"
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
      <br>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>
      <el-button
        v-waves       
        :loading="downloadLoading"
        class="filter-item"
        type="success"
        icon="el-icon-upload"
        @click="showTransferPopup()"
      >Transfer Balance</el-button>
      <el-button
        v-waves       
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
        icon="el-icon-plus"
        @click="dialogAddBalanceVisible=true"
      >Add Balance</el-button>
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
      <el-table-column label="Amount" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Balance" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.balance }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tran. Type" min-width="150px"align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.transaction.name | statusFilter">{{ row.transaction?row.transaction.name:''}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Transfer from" min-width="120px"align="right">
        <template slot-scope="{row}">
          <span >{{ row.transfered_from_user?row.transfered_from_user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Transfer to" min-width="120px"align="right">
        <template slot-scope="{row}">
          <span >{{ row.transfered_to_user?row.transfered_to_user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Transfer by" min-width="120px"align="right">
        <template slot-scope="{row}">
          <span >{{ row.transaction_by_user?row.transaction_by_user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Remark" min-width="150px"align="left">
        <template slot-scope="{row}">
          <span >{{ row.note }}</span>
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

    <el-dialog title="Transfer Balance" width="60%"  top="30px" :visible.sync="dialogTransferVisible">
      <el-form ref="formBalanceTransfer" :rules="rules" :model="temp">
        <el-row :gutter="20">
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="Transfer from (Member ID)" label-width="120" prop="transfer_from">
                <el-input  v-on:blur="handleFromCheckMemberId()" v-model="temp.transfer_from" ></el-input>
              </el-form-item>
              <el-form-item label="From Member Name" label-width="120" prop="transfer_from_name">
                <el-input  disabled v-model="temp.transfer_from_name" ></el-input>
              </el-form-item>
              <el-form-item label="Balance" prop="balance" label-width="120">
                <el-input  type="number" min=0  disabled v-model="temp.balance" ></el-input>
              </el-form-item>              
            </el-col>
            <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
              <el-form-item label="Transfer to (Member ID)" label-width="120" prop="transfer_to">
                <el-input  v-on:blur="handleToCheckMemberId()" v-model="temp.transfer_to" ></el-input>
              </el-form-item>
              <el-form-item label="Member Name" label-width="120" prop="transfer_to_name">
                <el-input  disabled v-model="temp.transfer_to_name" ></el-input>
              </el-form-item>
              <el-form-item label="Amount to transfer" prop="amount" label-width="120">
                <el-input  type="number" min=1  v-model="temp.amount" ></el-input>
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
        <el-button @click="dialogTransferVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="handleTransfer()">Transfer</el-button>
      </span>
    </el-dialog>

    <el-dialog title="Add Balance" width="60%"  top="30px" :visible.sync="dialogAddBalanceVisible">
      <el-form ref="formAddBalance" :rules="rulesAddBalance" :model="tempAddBalance">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Member ID" label-width="120" prop="member_id">
              <el-input  v-on:blur="handleAddMemberGetBalance()" v-model="tempAddBalance.member_id" ></el-input>
            </el-form-item>
            <el-form-item label="Member Name" label-width="120" prop="member_name">
              <el-input  disabled v-model="tempAddBalance.member_name" ></el-input>
            </el-form-item>
            
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Balance" prop="balance" label-width="120">
              <el-input  type="number" min=0  disabled v-model="tempAddBalance.balance" ></el-input>
            </el-form-item>
            <el-form-item label="Amount to add" prop="amount" label-width="120">
              <el-input  type="number" min=1  v-model="tempAddBalance.amount" ></el-input>
            </el-form-item>        
            <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="tempAddBalance.note"
                :rows="2"
                placeholder="Please Enter note">
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogAddBalanceVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="handleAddBalance()">Add Balance</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { fetchWalletTransfers, createTransfer, addBalance } from "@/api/admin/wallet";
import { getMemberBalance } from "@/api/admin/members";
import CountTo from 'vue-count-to'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "WalletTransfer",
  components: { Pagination,CountTo },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        'Withdrawal': "success",
        'Credit': "success",
        'Balance Transfer': "info",
        'Payout': "warning",
        'Debit': "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
        status:undefined,
        sort: "+id",
        date_range:''
      },
      balance:0,
      kyc_status:0,
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {       
        amount:undefined,
        balance:undefined,
        transfer_from:undefined,        
        transfer_from_name:undefined,
        transfer_to:undefined,        
        transfer_to_name:undefined,
        note:undefined,
      },
      tempAddBalance:{
        amount:undefined,
        balance:undefined,
        member_id:undefined,        
        member_name:undefined,
        note:undefined,
      },
      dialogAddBalanceVisible:false,
      dialogTransferVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {       
        amount: [
          { required: true, message: "Enter amount", trigger: "blur" }
        ],
        transfer_to: [
          {  required: true, message: "Transfer to is required.", trigger: "blur" }
        ],
        transfer_from: [
          {  required: true, message: "Transfer from is required.", trigger: "blur" }
        ],
      },
      rulesAddBalance: {       
        amount: [
          { required: true, message: "Enter amount", trigger: "blur" }
        ],
        member_id: [
          {  required: true, message: "Member Id is required.", trigger: "blur" }
        ]
      },
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
  },
  methods: {
    getList() {
      this.listLoading = true;     
      fetchWalletTransfers(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.balance = parseFloat(response.balance);
        this.kyc_status = response.kyc_status;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },
    handleFromCheckMemberId(){
      if(this.temp.transfer_from){
        getMemberBalance(this.temp.transfer_from).then((response) => {
          this.temp.transfer_from_name=response.data.name;
          this.temp.balance=response.data.member.wallet_balance;
        }).catch((error)=>{
          this.temp.transfer_from_name='';
          this.temp.transfer_from='';
        });
      }            
    },
    handleAddMemberGetBalance(){
       if(this.tempAddBalance.member_id){
        getMemberBalance(this.tempAddBalance.member_id).then((response) => {
          this.tempAddBalance.member_name=response.data.name;
          this.tempAddBalance.balance=response.data.member.wallet_balance;    
        }).catch((error)=>{
          this.tempAddBalance.member_id='';
          this.tempAddBalance.member_name='';
        });
      }    
    },
    handleToCheckMemberId(){
      if(this.temp.transfer_to){
        getMemberBalance(this.temp.transfer_to).then((response) => {
          this.temp.transfer_to_name=response.data.name;    
        }).catch((error)=>{
          this.temp.transfer_to_name='';
          this.temp.transfer_to='';
        });
      }            
    },
    showTransferPopup() {
      this.dialogTransferVisible = true;
      this.$nextTick(() => {
        this.$refs["formBalanceTransfer"].clearValidate();
      });      
    },
    handleAddBalance(){
      this.$refs["formAddBalance"].validate(valid => {
        if(parseFloat(this.temp.amount) <=0 ){
          this.$message.error('Amount cannot be 0.');
          return;
        }

        if (valid) {
          this.buttonLoading=true;
          addBalance(this.tempAddBalance).then((response) => {
            this.getList();
            this.resetTempAddBalance();
            this.dialogAddBalanceVisible = false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })
            this.buttonLoading=false;
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    handleTransfer(){
      this.$refs["formBalanceTransfer"].validate(valid => {
        if(parseFloat(this.temp.amount) <=0 ){
          this.$message.error('Withdrawal amount cannot be 0.');
          return;
        }
        if(parseFloat(this.balance) < parseFloat(this.temp.amount)){
          this.$message.error('Oops, You have not enough balance to transfer.');
          return;
        }
        if (valid) {
          this.buttonLoading=true;
          createTransfer(this.temp).then((response) => {
            this.getList();
            this.resetTemp();
            this.dialogTransferVisible = false;
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            })
            this.buttonLoading=false;
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    resetTempAddBalance(){
      this.tempAddBalance={
        amount:undefined,
        balance:undefined,
        member_id:undefined,        
        member_name:undefined,
        note:undefined,
      }
    },
    resetTemp() {
      this.temp = {
        amount:undefined,
        balance:undefined,
        transfer_from:undefined,        
        transfer_from_name:undefined,
        transfer_to:undefined,        
        transfer_to_name:undefined,
        note:undefined,
      };
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
          "Amount",
          "Balance",
          "Transfer from",
          "Transfer to",          
          "Transaction by",
          "Created at",
        ];
        const filterVal = [
          "id",
          "amount",
          "balance",
          "transfered_from",
          "transfered_to",
          "transaction_by",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "WalletTransfer"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "transfered_from") {
            return v.transfered_from_user.username
          }else if(j === "transfered_to") {
            return v.transfered_to_user.username
          }else if(j === "transaction_by") {
            return v.transaction_by_user.username
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
