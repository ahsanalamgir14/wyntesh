<template>
  <div class="app-container">
    <div class="filter-container">
      <el-date-picker v-model="listQuery.month" type="month" @change="handleFilter" format="yyyy-MM" size="mini" value-format="yyyy-MM" class="filter-item mobile_class" placeholder="Pick a month">
      </el-date-picker>
    </div>
    <div class="filter-container">
      <el-input size="mini" v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item mobile_class" @keyup.enter.native="handleFilter" />
      <el-button size="mini" v-waves class="filter-item " type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button size="mini" v-waves :loading="downloadLoading" class="filter-item" type="primary" icon="el-icon-download" @click="handleDownload">Export</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border show-summary :summary-method="getSummaries" fit highlight-current-row style="width: 100%;" >
      <el-table-column label="Sr#" prop="id" align="center" type="index" :index="indexMethod" width="70">
      </el-table-column>
      <el-table-column label="Date" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{d}-{m}-{y}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="250px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Pan" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.kyc?row.member.kyc.pan:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Duration Start" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Duration End" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_end_date }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payout Amount" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="TDS" width="130px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.tds }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Admin Fee" width="130px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.admin_fee }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Net Payable" width="130px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.net_payable_amount }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
  </div>
</template>
<script>
import { getMemberTDS, } from "@/api/admin/payouts";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "Payouts",
  components: { Pagination },
  directives: { waves },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      sums: {
        tds_amount: 0
      },
      listQuery: {
        page: 1,
        limit: 20,
        search: undefined,
        sort: "-id"
      },
      downloadLoading: false,
    };
  },
  created() {
    this.getList();
  },
  methods: {
    indexMethod(index) {
      let page = this.listQuery.page;
      if (this.listQuery.page == 1) {
        let tempIndex = index * 1;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      } else {

        let tempIndex = this.listQuery.limit * (page - 1) + index;;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      }
    },
    getList() {
      this.listLoading = true;
      getMemberTDS(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.sums = response.sum;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getSummaries(param) {
      const { columns, data } = param;
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 1) {
          sums[index] = 'Final Total (All)';
          return;
        }
        if (index === 7) {
          sums[index] = this.sums.total_payout_amount;
          return;
        }
        if (index === 8) {
          sums[index] = this.sums.total_tds;
          return;
        }
        if (index === 9) {
          sums[index] = this.sums.total_admin_fee;
          return;
        }
        if (index === 10) {
          sums[index] = this.sums.total_net_payable_amount;
          return;
        }
      });

      return sums;
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Month",
          "Member",
          "Name",
          "Pan",
          "Duration Start",
          "Duration End",
          "Payout Amount",
          "TDS",
          "Admin Fee",
          "Net Payable",
        ];
        const filterVal = [
          "id",
          "month",
          "member",
          "name",
          "pan",
          "duration_start",
          "duration_end",
          "payout_amount",
          "tds",
          "admin_fee",
          "net_payable_amount",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "TDS"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "month") {
            return v.payout.sales_start_date;
          } else if (j === "member") {
            return v.member.user.username;
          } else if (j === "name") {
            return v.member.user.name;
          } else if (j === "pan") {
            return v.member.kyc.pan;
          } else if (j === "duration_start") {
            return v.payout.sales_start_date;
          } else if (j === "duration_end") {
            return v.payout.sales_end_date;
          } else if (j === "tds") {
            return v.tds;
          } else {
            return v[j];
          }
        })
      );
    },
  }
};

</script>
<style scoped>
.pagination-container {
  margin-top: 5px;
  background: #fff;
  padding: 15px 16px;
}
</style>
