import request from '@/utils/request'

// User Endpoints

export function fetchUserOpenedList(query) {
  return request({
    url: '/user/tickets/opened',
    method: 'get',
    params: query
  })
}

export function fetchUserClosedList(query) {
  return request({
    url: '/user/tickets/closed',
    method: 'get',
    params: query
  })
}

export function openSupportTicket(data) {
  return request({
    url: '/user/tickets',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}

export function addUserConversationMessage(data) {
  return request({
    url: '/user/tickets/add/message',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}


export function getUserConversations(id) {
  return request({
    url: '/user/ticket/'+id+'/conversations',
    method: 'get'
  })
}


// Admin Endpoints

export function fetchOpenedList(query) {
  return request({
    url: '/admin/tickets/opened',
    method: 'get',
    params: query
  })
}

export function fetchClosedList(query) {
  return request({
    url: '/admin/tickets/closed',
    method: 'get',
    params: query
  })
}

export function getAdminConversations(id) {
  return request({
    url: '/admin/ticket/'+id+'/conversations',
    method: 'get'
  })
}

export function closeUserSupportTicket(data) {
  return request({
    url: '/admin/tickets/close',
    method: 'post',
    data
  })
}

export function addAdminConversationMessage(data) {
  return request({
    url: '/admin/tickets/add/message',
    method: 'post',
    data,
    headers: { "Content-Type": "multipart/form-data" }
  })
}
