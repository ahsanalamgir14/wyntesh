<template>
  <div class="app-container">
    <div class="filter-container">
      
        <el-input
          v-model="listQuery.member_id"
          placeholder="Member ID"
          style="width: 200px;"
          class="filter-item"
          @keyup.enter.native="handleFilter"
        />
        
        <el-select v-model="listQuery.transaction_type" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Transaction Type">
          <el-option
            v-for="item in transactionTypes"
            :key="item.name"
            :label="item.name"
            :value="item.id">
          </el-option>
        </el-select>
        <br>
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
     
      <el-table-column label="Member" min-width="120px"align="center">
        <template slot-scope="{row}">
          <span >{{ row.member?row.member.user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Transaction by" min-width="120px"align="center">
        <template slot-scope="{row}">
          <span >{{ row.transaction_by_user?row.transaction_by_user.username:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amout" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Balance" width="160px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.balance }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tran. Type" min-width="150px"align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.transaction.name | statusFilter">{{ row.transaction?row.transaction.name:''}}</el-tag>
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

  </div>
</template>

<script>
import { getLuxuryWalletTransactions } from "@/api/admin/wallet";
import { getTransactionTypes } from "@/api/user/config";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "WalletTransactions",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        'Withdrawal': "warning",
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
        search:undefined,
        sort: "-id",
        date_range:''
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      transactionTypes:[],
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
    this.getTransactionTypesList();
  },
  methods: {
    getList() {
      this.listLoading = true;
     
      getLuxuryWalletTransactions(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
   
    },
    getTransactionTypesList(){
      getTransactionTypes().then(response => {
        this.transactionTypes = response.data;
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
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Amount",
          "Balance",
          "Transaction Type",
          "Transfer from",
          "Transfer to",          
          "Transaction by",
          "Created at",
        ];
        const filterVal = [
          "id",
          "amount",
          "balance",
          "transaction_type_id",
          "transfered_from",
          "transfered_to",
          "transaction_by",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "WalletTransactions"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "transaction_type_id") {
            return v.transaction?v.transaction.name:''
          }else if(j === "transfered_from") {
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
