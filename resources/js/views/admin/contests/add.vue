<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="handleCreate"><i class="fas fa-plus"></i> Create New Contest</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button type="success" :loading="buttonLoading" icon="el-icon-check" circle @click="handleStartContest(row)"></el-button>
          <el-button type="primary" :loading="buttonLoading" icon="el-icon-edit" circle @click="handleEdit(row)"></el-button>
          <el-button icon="el-icon-delete" circle type="danger" @click="deleteData(row)"></el-button>
          <el-button icon="el-icon-s-flag" circle type="warning" @click="showContestRewards(row)"></el-button>
          <el-button icon="el-icon-trophy" circle type="success" @click="showContestBanners(row)"></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Running ?" min-width="150px">
        <template slot-scope="{row}">
          <span>{{ row.is_current?'Yes':'No' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Start date" width="150px" align="">
        <template slot-scope="{row}">
          <span>{{ row.start_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="End date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.end_date | parseTime('{y}-{m}-{d}')}}</span>
        </template>
      </el-table-column>
      <el-table-column label="Image" min-width="150px">
        <template slot-scope="{row}">
          <a :href="row.image" class="link-type" type="primary" target="_blank">View Image</a>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="textMap[dialogStatus]" width="60%" top="30px" :visible.sync="dialogContestVisible">
      <el-form ref="dataForm" :rules="rules" :model="temp">
        <el-row>
          <el-col :xs="24" :sm="12" :md="16" :lg="16" :xl="16">
            <el-form-item label="Name" prop="name">
              <el-input v-model="temp.name" />
            </el-form-item>
            <el-form-item label="Description" prop="description">
              <el-input type="textarea" :rows="4" placeholder="Description" v-model="temp.description">
              </el-input>
            </el-form-item>
            <el-form-item label="Number of Winners" prop="number_of_winners">
              <el-input type="number" v-model="temp.number_of_winners" />
            </el-form-item>
            <el-form-item label="Start Date" prop="start_date">
              </br>
              <el-date-picker v-model="temp.start_date" type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd" placeholder="Start Date">
              </el-date-picker>
            </el-form-item>
            <el-form-item label="End Date" prop="end_date">
              </br>
              <el-date-picker v-model="temp.end_date" type="date" format="yyyy-MM-dd" value-format="yyyy-MM-dd" placeholder="End Date">
              </el-date-picker>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="12" :md="16" :lg="8" :xl="8">
            <div class="img-upload">
              <el-form-item prop="image">
                <label for="Image">Image</label>
                <el-upload class="avatar-uploader" action="#" ref="upload" :show-file-list="true" :auto-upload="false" :on-change="handleChange" :on-remove="handleRemove" :limit="3" :file-list="fileList" :on-exceed="handleExceed" accept="image/png, image/jpeg">
                  <img v-if="temp.image" :src="temp.image" class="avatar">
                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
                <p>Click to upload image.</p>
              </el-form-item>
            </div>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogContestVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
    <el-dialog title="Add Award" width="30%" top="30px" :visible.sync="dialogContestRewardCreateVisible">
      <el-form ref="dataRewardForm" :rules="rulesReward" :model="reward">
        <el-row>
          <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
            <el-form-item label="Title" prop="title">
              <el-input v-model="reward.title" />
            </el-form-item>
            <el-form-item label="Member ID" prop="member_id">
              <el-input v-model="reward.member_id" />
            </el-form-item>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogContestRewardCreateVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createReward():updateReward()">
          Save
        </el-button>
      </div>
    </el-dialog>
    <el-dialog title="Contest Awards" width="60%" top="30px" :visible.sync="dialogContestRewardsVisible">
      <div class="filter-container">
        <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="handleCreateContestReward"><i class="fas fa-plus"></i> Add New Award</el-button>
      </div>
      <el-row>
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-table :key="tableKey" v-loading="listLoading" :data="contestRewardsList" border fit highlight-current-row style="width: 100%;">
            <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80">
              <template slot-scope="{row}">
                <span>{{ row.id }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
              <template slot-scope="{row}">
                <el-button type="primary" :loading="buttonLoading" icon="el-icon-edit" circle @click="handleRewardEdit(row)"></el-button>
                <el-button icon="el-icon-delete" circle type="danger" @click="deleteRewardData(row)"></el-button>
              </template>
            </el-table-column>
            <el-table-column label="Title" min-width="150px">
              <template slot-scope="{row}">
                <span>{{ row.title }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Member" min-width="150px">
              <template slot-scope="{row}">
                <span>{{ row.member.user.name }} ({{ row.member.user.username }})</span>
              </template>
            </el-table-column>
          </el-table>
        </el-col>
      </el-row>
    </el-dialog>
    <el-dialog title="Add Popup" width="60%" top="30px" :visible.sync="dialogContestBannerCreateVisible">
      <el-form ref="dataBannerForm" :rules="rulesBanner" :model="banner">
        <el-row>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <el-form-item label="Rank" prop="rank_id">
              <br>
              <el-select v-model="banner.rank_id" filterable placeholder="Rank" >
                <el-option v-for="item in ranks" :key="item.name" :label="item.name" :value="item.id">
                </el-option>
            </el-select>
            </el-form-item>
          </el-col>
          <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
            <div class="img-upload">
              <el-form-item prop="image">
                <label for="Image">Image</label>
                <el-upload class="avatar-uploader" action="#" ref="upload" :show-file-list="true" :auto-upload="false" :on-change="handleChange" :on-remove="handleRemove" :limit="3" :file-list="fileList" :on-exceed="handleExceed" accept="image/png, image/jpeg">
                  <img v-if="banner.image" :src="banner.image" class="avatar">
                  <i v-else class="el-icon-plus avatar-uploader-icon"></i>
                </el-upload>
                <p>Click to upload image.</p>
              </el-form-item>
            </div>
          </el-col>
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogContestBannerCreateVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="createBanner()">
          Save
        </el-button>
      </div>
    </el-dialog>
    <el-dialog title="Contest Popups" width="60%" top="30px" :visible.sync="dialogContestBannersVisible">
      <div class="filter-container">
        <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="handleCreateContestBanner"><i class="fas fa-plus"></i> Add Popup</el-button>
      </div>
      <el-row>
        <el-col :xs="24" :sm="24" :md="24" :lg="24" :xl="24">
          <el-table :key="tableKey" v-loading="listLoading" :data="contestBannersList" border fit highlight-current-row style="width: 100%;">
            <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">
              <template slot-scope="{row}">
                <el-button icon="el-icon-delete" circle type="danger" @click="deleteBannerData(row)"></el-button>
              </template>
            </el-table-column>
            <el-table-column label="Rank" min-width="150px">
              <template slot-scope="{row}">
                <span>{{ row.rank.name }}</span>
              </template>
            </el-table-column>
            <el-table-column label="Image" min-width="150px">
              <template slot-scope="{row}">
                <a v-if="row.image" :href="row.image" target="_blank">View image.</a>
              </template>
            </el-table-column>
          </el-table>
        </el-col>
      </el-row>
    </el-dialog>
  </div>
</template>
<script>
import {
  getContests,
  deleteContest,
  createContest,
  updateContest,
  startContest,
  getContestRewards,
  createSpecialReward,
  updateSpecialReward,
  deleteContestSpecialReward,
  getContestBanners,
  createBanner,
  deleteBanner,

} from "@/api/admin/contests";
import waves from "@/directive/waves";
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";
import Tinymce from '@/components/Tinymce'
import {
  getAllRanks,
} from "@/api/admin/ranks";

export default {
  name: "contests",
  components: { Pagination, Tinymce },
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
      contestRewardsList: null,
      contestBannersList: null,
      list: null,
      ranks:[],
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "+id"
      },
      fileList: [],
      file: undefined,
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        description: undefined,
        start_date: undefined,
        end_date: undefined,
        number_of_winners: 1,
        image: undefined
      },
      reward: {
        title: undefined,
        member_id: undefined,
        contest_id: undefined,
      },
      banner: {
        rank_id: undefined,
        contest_id: undefined,
      },
      contest_id: undefined,
      dialogContestVisible: false,
      dialogContestRewardsVisible: false,
      dialogContestRewardCreateVisible: false,
      dialogContestBannerCreateVisible: false,
      dialogContestBannersVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        name: [{ required: true, message: 'Name is required', trigger: 'blur' }],
        start_date: [{ required: true, message: 'Start date is required', trigger: 'blur' }],
        end_date: [{ required: true, message: 'End date is required', trigger: 'blur' }],
        number_of_winners: [{ required: true, message: 'Number of winner is required', trigger: 'blur' }]
      },
      rulesReward: {
        title: [{ required: true, message: 'Title is required', trigger: 'blur' }],
        member_id: [{ required: true, message: 'Member ID is required', trigger: 'blur' }],
      },
      rulesBanner: {
        rank_id: [{ required: true, message: 'Select Rank', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getAllRanks();
  },
  methods: {
    getAllRanks() {
      getAllRanks().then(response => {
        this.ranks = response.data;
      });
    },
    handleChange(f, fl) {
      if (fl.length > 1) {
        fl.shift()
      }
      this.file = f.raw
    },
    handleRemove(file, fileList) {
      this.file = undefined;
      this.fileList = [];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
    },
    getList() {
      this.listLoading = true;
      getContests(this.listQuery).then(response => {
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
    resetTemp() {
      this.temp = {
        name: undefined,
        description: undefined,
        start_date: undefined,
        end_date: undefined,
        number_of_winners: 1,
        image: undefined
      };
      this.file = undefined
      this.fileList = [];
    },
    handleCreate() {
      this.fileList = [];
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogContestVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    handleCreateContestReward() {
      this.reward.contest_id = this.contest_id
      this.dialogStatus = "create";
      this.reward.title = undefined;
      this.reward.member_id = undefined;
      this.dialogContestRewardCreateVisible = true;
      this.$nextTick(() => {
        this.$refs["dataRewardForm"].clearValidate();
      });
    },
    handleCreateContestBanner() {
      this.banner.contest_id = this.contest_id
      this.dialogStatus = "create";
      this.banner.rank_id = undefined;
      this.fileList = [];
      this.dialogContestBannerCreateVisible = true;
      this.$nextTick(() => {
        this.$refs["dataBannerForm"].clearValidate();
      });
    },
    showContestBanners(contest) {
      this.contest_id = contest.id;
      this.getContestBanners();
    },
    getContestBanners() {
      this.listLoading = true;
      let data = {
        contest_id: this.contest_id,
      }
      getContestBanners(data).then(response => {
        this.contestBannersList = response.data.data;
        this.dialogContestBannersVisible = true;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    showContestRewards(contest) {
      this.contest_id = contest.id;
      this.contestRewards();
    },
    contestRewards() {
      this.listLoading = true;
      let data = {
        contest_id: this.contest_id,
      }
      getContestRewards(data).then(response => {
        this.contestRewardsList = response.data.data;
        this.dialogContestRewardsVisible = true;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    createBanner() {

      this.$refs["dataBannerForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          let form_data = this.banner;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);

          createBanner(form).then((data) => {
            this.getContestBanners();
            this.dialogContestBannerCreateVisible = false;
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
    deleteBannerData(row) {
      deleteBanner(row.id).then((data) => {
        this.dialogContestVisible = false;
        this.$notify({
          title: "Success",
          message: data.message,
          type: "success",
          duration: 2000
        });
        this.getContestBanners();
      });
    },
    createData() {

      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          let form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);

          createContest(form).then((data) => {
            this.getList();
            this.dialogContestVisible = false;
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
    handleEdit(row) {
      this.fileList = [];
      this.file = undefined;
      this.temp = Object.assign({}, row); // copy obj
      if (row.is_visible == 1) {
        this.temp.is_visible = true
      } else {
        this.temp.is_visible = false
      }
      this.dialogStatus = "update";
      this.dialogContestVisible = true;
      this.$nextTick(() => {
        this.$refs["dataForm"].clearValidate();
      });
    },
    updateData() {

      this.$refs["dataForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          const tempData = Object.assign({}, this.temp);

          for (var key in tempData) {
            if (tempData[key] !== undefined && tempData[key] !== null) {
              form.append(key, tempData[key]);
            }
          }

          form.append('image', this.file);


          updateContest(form).then((data) => {
            this.getList();
            this.dialogContestVisible = false;
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
    createReward() {
      this.$refs["dataRewardForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          createSpecialReward(this.reward).then((data) => {
            this.contestRewards();
            this.dialogContestRewardCreateVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleRewardEdit(row) {
      this.reward = Object.assign({}, row); // copy obj
      this.reward.member_id = row.member.user.username;
      this.dialogStatus = "update";
      this.dialogContestRewardCreateVisible = true;
      this.$nextTick(() => {
        this.$refs["dataRewardForm"].clearValidate();
      });
    },
    updateReward() {

      this.$refs["dataRewardForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          updateSpecialReward(this.reward).then((data) => {
            this.contestRewards();
            this.dialogContestRewardCreateVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading = false;
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });

    },
    deleteRewardData(row) {
      deleteContestSpecialReward(row.id).then((data) => {
        this.dialogContestVisible = false;
        this.$notify({
          title: "Success",
          message: data.message,
          type: "success",
          duration: 2000
        });
        this.contestRewards();
      });
    },
    handleStartContest(row) {

      this.$confirm('Are you sure you want Start Contest?', 'Warning', {
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        this.buttonLoading = true;

        startContest(row.id).then((data) => {
          this.dialogContestVisible = false;
          this.buttonLoading = false;
          this.$notify({
            title: "Success",
            message: data.message,
            type: "success",
            duration: 2000
          });
          this.getList();
        }).catch((err) => {
          this.buttonLoading = false;
        });

      })


    },
    deleteData(row) {
      deleteContest(row.id).then((data) => {
        this.dialogContestVisible = false;
        this.$notify({
          title: "Success",
          message: data.message,
          type: "success",
          duration: 2000
        });
        this.getList();
      });
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
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

@media (min-width:750px) {
  .img-upload {
    float: right;
    margin-right: 20px;
  }
}

</style>
