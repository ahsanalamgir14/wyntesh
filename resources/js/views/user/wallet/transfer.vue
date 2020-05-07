<template>
  <div class="app-container">
    <el-row :gutter="40" class="panel-group">

      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <div class="card-panel" >
          <div class="card-panel-icon-wrapper icon-message">
            <i class="fas fa-wallet card-panel-icon" style="color: #27AE60;" ></i>
          </div>
          <div class="card-panel-description">
            
            <count-to :start-val="0" :end-val="balance" :duration="100" class="card-panel-num" />
            <div class="card-panel-text">
              Wallet Balance
            </div>
          </div>
        </div>
      </el-col>
    </el-row>
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
        :disabled="!kyc_status"
        :loading="downloadLoading"
        class="filter-item"
        type="success"
        icon="el-icon-upload"
        @click="shotTransferPopup()"
      >{{ kyc_status?'Transfer Balance':'Verify your KYC First to ransfer Balance'}}</el-button>
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

    <el-dialog title="Transfer Balance" width="40%"  top="30px" :visible.sync="dialogTransferVisible">
      <el-form ref="formBalanceTransfer" :rules="rules" :model="temp">
        <el-form-item label="Transfer to (Memmber ID)" label-width="120" prop="transfer_to">
          <el-input  v-on:blur="handleCheckMemberId()" v-model="temp.transfer_to" ></el-input>
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
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogTransferVisible = false">Cancel</el-button>
        <el-button type="primary" @click="handleTransfer()">Confirm</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import { fetchWalletTransfers, createTransfer } from "@/api/user/wallet";
import { checkMemberCode } from "@/api/user/members";
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
        Approved: "success",
        Pending: "info",
        Rejected: "danger"
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
        limit: 5,
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
        transfer_to:undefined,
        note:undefined,
        transfer_to_name:undefined,
      },
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
      this.listLoading = false;     
      fetchWalletTransfers(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.balance = parseFloat(response.balance);
        this.kyc_status = response.kyc_status;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleCheckMemberId(){
      if(this.temp.transfer_to){
        checkMemberCode(this.temp.transfer_to).then((response) => {
          this.temp.transfer_to_name=response.data.name;    
        }).catch((error)=>{
          this.temp.transfer_to_name='';
          this.temp.transfer_to='';
        });
      }            
    },
    shotTransferPopup() {
      this.dialogTransferVisible = true;
      this.$nextTick(() => {
        this.$refs["formBalanceTransfer"].clearValidate();
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
          })
        }
      });
    },
    resetTemp() {
      this.temp = {
        amount:undefined,
        transfer_to:undefined,
        note:undefined,
        transfer_to_name:undefined,
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

.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);

    .card-panel-icon-wrapper {
      float: left;
      margin: 10px 0 0 10px;
      padding: 5px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 35px;
      border-style: solid;
      border-width: thin;
      padding:5px;
      height: 35px;
      width: 45px;
    }
    @media (min-width:550px) {
      .card-panel-description {
        float: right;
        font-weight: bold;
        margin-top: 15px;
        margin-right: 10px;
        width: 90%;

        .card-panel-text {
          line-height: 25px;
          color: rgba(0, 0, 0, 0.45);
          font-size: 14px;
          display: block;
          margin-top: 5px;
        }

        .card-panel-num {
          font-size: 25px;
          float:right;
          display: block;
        }
      }
    }
  }
}

@media (max-width:550px) {
  .card-panel{
    .card-panel-description {
        font-weight: bold;
          margin: 5px auto !important;
          float: none !important;
          text-align: center;
        .card-panel-text {
          line-height: 20px;
          color: rgba(0, 0, 0, 0.45);
          font-size: 10px;
        }

        .card-panel-num {
          display: block;
          font-size: 20px;
        
        }
      }
  }

  .card-panel-icon-wrapper {
    float: none !important;
    margin: 0 !important;

    svg {
      display: block;
      margin: 5px auto !important;
      float: none !important;
    }
  }
}

</style>
