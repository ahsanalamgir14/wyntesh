<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-refresh-left" @click="handleCreate"> Add Rewars</el-button>
      <!--   <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button> -->
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" class-name="status-col" width="200">
        <template slot-scope="{row}">
          <el-tag>{{ row.amount }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="TDS Percent" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.tds_percent }}</span>
        </template>
      </el-table-column>
      <el-table-column label="TDS Amount" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.tds_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Final Amount" width="130px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.final_amount }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="dialogTitle" width="50%" top="30px" :visible.sync="dialogAddReward">
      <el-form ref="addRewards" :rules="rules" :model="temp" style="">
        <el-row :gutter="20">
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-form-item prop="name" label="Reward Name">
              <el-input v-model="temp.name" name="name" type="text" auto-complete="on" placeholder="Enter Reward Name" />
            </el-form-item>
            <el-form-item prop="member_id" label="Member Id">
              <el-input v-model="temp.member_id" name="name" type="text" auto-complete="on" placeholder="Enter Member Id" @change="handleCheckmemberExits" />
            </el-form-item>
            <el-form-item prop="member_name" label="Member Name">
              <el-input disabled v-model="temp.member_name" name="name" type="text" auto-complete="on" placeholder="Member Name" />
            </el-form-item>
            <el-form-item prop="amount" label="Amount">
              <el-input v-model="temp.amount" name="amount" type="number" auto-complete="on" placeholder="Enter Amount" @change="handleTdsAmountCal" />
            </el-form-item>
            <el-form-item prop="tds_percent" label="TDS Percent">
              <el-input disabled v-model="temp.tds_percent" name="tds_percent" type="text" auto-complete="on" placeholder="Enter TDS Percent." />
            </el-form-item>
            <el-form-item prop="tds_amount" label="TDS Amount">
              <el-input disabled v-model="temp.tds_amount" name="name" type="text" auto-complete="on" placeholder="Enter TDS Amount" />
            </el-form-item>
            <el-form-item prop="final_amount" label="Final Amount">
              <el-input disabled v-model="temp.final_amount" name="name" type="text" auto-complete="on" placeholder="Enter Reward Name" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogAddReward = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Give
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import { fetchRewards, checkMember, addRewrds } from "@/api/admin/payouts";
import { getAllIncomes, } from "@/api/admin/incomes";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import role from '@/directive/role';
import checkRole from '@/utils/role';

export default {
  name: "GeneratePayout",
  components: { Pagination },
  directives: { waves, role },
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
      temp: {
        name: undefined,
        amount: undefined,
        tds_percent: 0,
        tds_amount: 0,
        member_id: undefined,
        mem_id: undefined,
        member_name: undefined,
        final_amount: 0
      },
      tds_percent: 0,
      income_list: [],
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
      dialogAddReward: false,
      dialogStatus: "",
      dialogTitle: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'Reward name is required', trigger: 'blur' }],
        member_id: [{ required: true, message: 'Member id require', trigger: 'blur' }],
        amount: [{ required: true, message: 'Amount is require', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    getAllIncomes().then(response => {
      this.income_list = response.data;
    });
  },
  methods: {
    checkRole,
    getList() {
      this.listLoading = true;
      fetchRewards(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.tds_percent = response.tds;

        this.temp.tds_percent = this.tds_percent
        // alert(response.tds);
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleCheckmemberExits() {
      checkMember(this.temp.member_id).then(response => {
        // console.log(response.data.member.id)
        this.temp.member_name = response.data.name;
        this.temp.mem_id = response.data.member.id;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((err) => {
        this.temp.member_id = "";
      });;
    },
    handleTdsAmountCal() {
      let tds = (this.temp.amount * this.tds_percent) / 100
      this.temp.tds_amount = parseFloat(tds);
      this.temp.final_amount = (parseFloat(this.temp.amount, 10) - parseFloat(tds, 10));
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },

    resetTemp() {
      this.temp = {
        name: undefined,
        amount: undefined,
        tds_percent: 0,
        tds_amount: 0,
        member_id: undefined,
        mem_id: undefined,
        member_name: undefined,
        final_amount: 0
      };
      this.temp.tds_percent = this.tds_percent
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle = "Rewards";
      this.dialogAddReward = true;
      this.$nextTick(() => {
        this.$refs["addRewards"].clearValidate();
      });
    },
    createData() {
      this.$refs["addRewards"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          addRewrds(this.temp).then((data) => {
            this.dialogAddReward = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
            this.resetTemp();
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
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
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Sr.No",
          "Payout Type",
          "Generated by System ?",
          "Sales start date",
          "Sales end date",
          "Sales BV",
          "Saled Amount",
          "Total Payout",
          "Generated At",
        ];
        const filterVal = [
          "id",
          "payout_type",
          "is_run_by_system",
          "sales_start_date",
          "sales_end_date",
          "sales_bv",
          "sales_amount",
          "total_payout",
          "created_at",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "payouts"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if (j === "payout_type") {
            return v.payout_type ? v.payout_type.name : ''
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
