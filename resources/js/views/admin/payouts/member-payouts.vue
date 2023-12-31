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
        type="warning"
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
       sortable="custom"
      show-summary
      :summary-method="getSummaries"
      style="width: 100%;"
      @sort-change="sortChange"
    >
      <el-table-column
        label="ID"
        prop="id"
     
        show-summary
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
     <!--   <el-table-column label="Actions" align="center" width="100" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="warning"
            :loading="buttonLoading"
            circle
            icon="el-icon-printer"
            @click="payoutReport(row.id)"
          ></el-button>
        </template>
      </el-table-column> -->
      
      <el-table-column label="Member" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payout Duration" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}-{d}') }} - {{ row.payout.sales_end_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Matched BV" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.total_matched_bv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.payout_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="TDS" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.tds }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Admin Fee" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.admin_fee }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Net Payable" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.net_payable_amount }}</span>
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
import { getMemberPayouts, } from "@/api/admin/payouts";
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
      sums: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 10,
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
        if (index === 3) {
          sums[index] = 'Final Total (All)';
          return;
        }
       
        if (index === 5) {
          sums[index] = this.sums.total_payout_amount;
          return;
        }
        if (index === 6) {
          sums[index] = this.sums.total_tds;
          return;
        }
        if (index === 7) {
          sums[index] = this.sums.total_admin_fee;
          return;
        }
        if (index === 8) {
          sums[index] = this.sums.total_net_payable_amount;
          return;
        }
      });
      return sums;
    },
    getList() {
      this.listLoading = true;
      getMemberPayouts(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.sums = response.sum;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    payoutReport(id){
      let routeData = this.$router.resolve({path: '/payout-report/'+id});
      window.open(routeData.href, '_blank');
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
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Sr.No",
          "Member",
          "Name",
          "Sales start date",
          "Sales end date",
          "Matched BV",
          "Amount",
          "TDS",
          "Admin Fee",
          "Net Payable",
        ];
        const filterVal = [
          "id",
          "member",
          "name",
          "sales_start_date",
          "sales_end_date",
          "total_matched_bv",
          "payout_amount",
          "tds",
          "admin_fee",
          "net_payable_amount",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "member-payouts"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "member") {
            return v.member?v.member.user.username:''
          }else if(j === "name") {
            return v.member?v.member.user.name:''
          }else if(j === "sales_start_date") {
            return v.payout? parseTime(v.payout.sales_start_date,'{y}-{m}-{d}'):''
          }else if(j === "sales_end_date") {
            return v.payout? parseTime(v.payout.sales_end_date,'{y}-{m}-{d}'):''
          }else {
            return v[j];
          }
        })
      );
    },
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
