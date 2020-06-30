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
      style="width: 100%;"
      @sort-change="sortChange"
    > 
      <el-table-column label="Actions" align="center" width="230px" class-name="small-padding">        
        <template slot-scope="{row}">
          <el-tooltip content="Release withhold Payout" placement="right" effect="dark" >
            <el-button
              circle
              type="success"
              icon="el-icon-check"
              :loading="buttonLoading"
              @click="handleReleasePayout(row)"
              ></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="Member" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      
      <el-table-column label="Month" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="BV to release" min-width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.required_bv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.withhold_amount }}</span>
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
import { getMemberIncomeHoldings, releaseMemberHoldPayout} from "@/api/admin/payouts";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "member-income-holdings",
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
    getList() {
      this.listLoading = true;
      getMemberIncomeHoldings(this.listQuery).then(response => {
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
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    },
    handleReleasePayout(row){

      this.$confirm('Are you sure you want to release withhold payout?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.buttonLoading=true;
        let data={
          payout_id:row.payout_id,
          member_id:row.member_id
        };
        releaseMemberHoldPayout(data).then((data) => {
          this.buttonLoading=false;
          this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
          });
          this.getList();
        }).catch((err)=>{
          this.buttonLoading=false;
        });
      }).catch(()=>{
          
      });
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Payout",
          "Member",
          "Required BV to release",
          "Amount"
        ];
        const filterVal = [          
          "payout",
          "member",
          "required_bv",
          "amount",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "income-holdings"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "payout") {
            return v.payout?v.payout.created_at:''
          }else if(j === "member") {
            return v.member?v.member.user.username:''
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
