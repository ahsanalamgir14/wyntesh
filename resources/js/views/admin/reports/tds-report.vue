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
      <el-date-picker
        v-model="listQuery.month"
        type="month"
        @change="handleFilter"
        format="yyyy-MM"
        value-format="yyyy-MM"
         class="filter-item"
        placeholder="Pick a month">
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
       show-summary
      :summary-method="getSummaries"
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        type="index"
        width="50">
      </el-table-column>      
      <el-table-column label="Month" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="250px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Pan" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.kyc?row.member.kyc.pan:'' }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Duration Start" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.payout.sales_start_date }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Duration End" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.payout.sales_end_date }}</span>
        </template>
      </el-table-column>
      
      <el-table-column label="TDS" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ parseFloat(row.tds)+parseFloat(row.affiliate_tds) }}</span>
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
import { getMemberTDS, } from "@/api/admin/payouts";
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
      sum:0,
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
          "TDS",
        ];
        const filterVal = [
          "id",
          "month",
          "member",
          "name",
          "pan",
          "duration_start",
          "duration_end",
          "tds",
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
          }else if (j === "member") {
            return v.member.user.username;
          }else if (j === "name") {
            return v.member.user.name;
          }else if (j === "pan") {
            return v.member.kyc?v.member.kyc.pan:'';
          }else if (j === "duration_start") {
            return v.payout.sales_start_date;
          }else if (j === "duration_end") {
            return v.payout.sales_end_date;
          }else if (j === "tds") {
            return parseFloat(v.tds)+parseFloat(v.affiliate_tds);
          }else{
            return v[j];
          }
        })
      );
    },
    getList() {
      this.listLoading = true;
      getMemberTDS(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.sums=response.sum;
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
        if(index===7){
          sums[index] = this.sums?this.sums.tds_amount:0;
          return; 
        }
      });

      return sums;
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
