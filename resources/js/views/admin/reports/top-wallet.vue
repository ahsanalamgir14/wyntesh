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
      show-summary
      :summary-method="getSummaries"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        type="index"
        width="50" label="#">
      </el-table-column>      

      <el-table-column label="Member Id" min-width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.user.username }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Member Name" min-width="200px" align="center">
        <template slot-scope="{row}">
          <span >{{ row.user.name }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Wallet Balance" prop="id" sortable="custom" min-width="200px" align="center" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span >{{ row.wallet_balance }}</span>
        </template>
      </el-table-column>

       <el-table-column label="Income Wallet Balance" min-width="200px" align="center" >
        <template slot-scope="{row}">
          <span >{{ row.income_wallet_balance }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Luxury Wallet Balance" min-width="200px" align="center" >
        <template slot-scope="{row}">
          <span >{{ row.luxury_wallet_balance }}</span>
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
import { getMemberTopWallet, } from "@/api/admin/payouts";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "Payouts",
  components: { Pagination },
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
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      sums:{
        total_wallet_balance:0,
        total_income_wallet_balance:0,
        total_luxury_wallet_balance:0,
      },
      listQuery: {
        page: 1,
        limit: 20,
        search: undefined,
        sort: "-id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
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
      dialogPayoutGenerateVisible:false,
      dialogStatus: "",
      dialogTitle:"",
      textMap: {
        update: "Edit",
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
    getSummaries(param) {
      const { columns, data } = param;
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 1) {
          sums[index] = 'Final Total (All)';
          return;
        }
        if (index === 3) {
          sums[index] = this.sums.total_wallet_balance;
          return;
        }
        if (index === 4) {
          sums[index] = this.sums.total_income_wallet_balance;
          return;
        }
        if (index === 5) {
          sums[index] = this.sums.total_luxury_wallet_balance;
          return;
        }
      });

      return sums;
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Member Id",
          "Member Name",
          "Wallet Balance",
        ];
        const filterVal = [
          "username",
          "name",
          "wallet_balance", 
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "tds"
        });
        this.downloadLoading = false;
      });
    },formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "month") {
            return v.payout.sales_start_date;
          }else if (j === "username") {
            return v.user.username;
          }else if (j === "name") {
            return v.user.name;
          }else if (j === "wallet_balance") {
            return v.wallet_balance;
          }else{
            return v[j];
          }
        })
      );
    },
    getList() {
      this.listLoading = true;
      getMemberTopWallet(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.sums=response.sum;
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

</style>
