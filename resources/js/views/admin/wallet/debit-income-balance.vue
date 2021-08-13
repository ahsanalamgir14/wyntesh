<template>
  <div class="app-container">
    
    <div class="filter-container">
       <el-input
          v-model="listQuery.transfered_from"
          placeholder="Debited from"
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
        type="warning"
        icon="el-icon-minus"
        @click="dialogDebitBalanceVisible=true"
      >Debit Income Balance</el-button>
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
      <el-table-column label="Debited from" min-width="120px"align="right">
        <template slot-scope="{row}">
          <span >{{ row.transfered_from_user?row.transfered_from_user.username:'' }}</span>
        </template>
      </el-table-column>
     <!--  <el-table-column label="Transfer to" min-width="120px"align="right">
        <template slot-scope="{row}">
          <span >{{ row.transfered_to_user?row.transfered_to_user.username:'' }}</span>
        </template>
      </el-table-column> -->
      <el-table-column label="Debited by" min-width="120px"align="right">
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

    <el-dialog title="Debit Income Balance" width="60%"  top="30px" :visible.sync="dialogDebitBalanceVisible">
      <el-form ref="formDebitBalance" :rules="rulesDebitBalance" :model="tempDebitBalance">
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Member ID" label-width="120" prop="member_id">
              <el-input  v-on:blur="handleAddMemberGetBalance()" v-model="tempDebitBalance.member_id" ></el-input>
            </el-form-item>
            <el-form-item label="Member Name" label-width="120" prop="member_name">
              <el-input  disabled v-model="tempDebitBalance.member_name" ></el-input>
            </el-form-item>
            
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Balance" prop="balance" label-width="120">
              <el-input  type="number" min=0  disabled v-model="tempDebitBalance.balance" ></el-input>
            </el-form-item>
            <el-form-item label="Amount to debit" prop="amount" label-width="120">
              <el-input  type="number" min=1  v-model="tempDebitBalance.amount" ></el-input>
            </el-form-item>        
            <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="tempDebitBalance.note"
                :rows="2"
                placeholder="Please Enter note">
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogDebitBalanceVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="handleDebitBalance()">Debit Income Balance</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { getIncomeWalletDebitTransactions, debitIncomeBalance } from "@/api/admin/wallet";
import { getMemberIncomeBalance } from "@/api/admin/members";
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
      tempDebitBalance:{
        amount:undefined,
        balance:undefined,
        member_id:undefined,        
        member_name:undefined,
        note:undefined,
      },
      dialogDebitBalanceVisible:false,
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
      rulesDebitBalance: {       
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
      getIncomeWalletDebitTransactions(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },
    handleFromCheckMemberId(){
      if(this.temp.transfer_from){
        getMemberIncomeBalance(this.temp.transfer_from).then((response) => {
          this.temp.transfer_from_name=response.data.name;
          this.temp.balance=response.data.member.income_wallet_balance;
        }).catch((error)=>{
          this.temp.transfer_from_name='';
          this.temp.transfer_from='';
        });
      }            
    },
    handleAddMemberGetBalance(){
       if(this.tempDebitBalance.member_id){
        getMemberIncomeBalance(this.tempDebitBalance.member_id).then((response) => {
          this.tempDebitBalance.member_name=response.data.name;
          this.tempDebitBalance.balance=response.data.member.income_wallet_balance;    
        }).catch((error)=>{
          this.tempDebitBalance.member_id='';
          this.tempDebitBalance.member_name='';
        });
      }    
    },
    handleToCheckMemberId(){
      if(this.temp.transfer_to){
        getMemberIncomeBalance(this.temp.transfer_to).then((response) => {
          this.temp.transfer_to_name=response.data.name;    
        }).catch((error)=>{
          this.temp.transfer_to_name='';
          this.temp.transfer_to='';
        });
      }            
    },
    
    handleDebitBalance(){
      this.$refs["formDebitBalance"].validate(valid => {
        if(parseFloat(this.temp.amount) <=0 ){
          this.$message.error('Amount cannot be 0.');
          return;
        }

        if(parseFloat(this.temp.amount) < this.temp.balance ){
          this.$message.error('Member does not have enough balance');
          return;
        }

        if (valid) {
          this.buttonLoading=true;
          debitIncomeBalance(this.tempDebitBalance).then((response) => {
            this.getList();
            this.resetTempDebitBalance();
            this.dialogDebitBalanceVisible = false;
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
    
    resetTempDebitBalance(){
      this.tempDebitBalance={
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
            return v.transfered_from_user?v.transfered_from_user.username:''
          }else if(j === "transfered_to") {
            return v.transfered_to_user?v.transfered_to_user.username:''
          }else if(j === "transaction_by") {
            return v.transaction_by_user?v.transaction_by_user.username:''
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
