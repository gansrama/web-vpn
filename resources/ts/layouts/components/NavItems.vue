<script lang="ts" setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
</script>

<template>
  <!-- 👉 Dashboards -->
  <VerticalNavGroup
    :item="{
      title: 'Dashboards',
      icon: 'ri-home-smile-line',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Analytics',
        to: '/dashboard',
      }"
    />
    
    
  </VerticalNavGroup>

  

  <!-- 👉 Apps & Pages -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Form & Pages',
    }"
  />
  <!-- 👉 Front Pages -->
  <VerticalNavGroup
    :item="{
      title: 'Form VPN',
      icon: 'ri-file-copy-line',
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Form Akses Logic',
        to: '/form-akses-logic',
        icon: 'ri-key-line',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Form Teleworking',
        to: '/form-teleworking',
        icon: 'ri-home-office-line',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Form Google',
        to: '/form-google',
        icon: 'ri-google-line',
      }"
    />
  </VerticalNavGroup>

  <!-- 👉 Master & Data -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Master & Data',
    }"
  />
  <VerticalNavLink
      :item="{
        title: 'Data Pegawai',
        to: '/data-pegawai',
        icon: 'ri-user-3-line',
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Data IP Address',
        to: '/data-ip-address',
        icon: 'ri-global-line',
      }"
    />
  <VerticalNavLink
      :item="{
        title: 'Data Akses Logic',
        to: '/data-akses-logic',
        icon: 'ri-key-2-line',
      }"
    />
  <VerticalNavLink
      :item="{
        title: 'Data Teleworking',
        to: '/data-teleworking',
        icon: 'ri-computer-line',
      }"
    />
  <VerticalNavLink
      :item="{
        title: 'Status Form Pegawai',
        to: '/status-form-pegawai',
        icon: 'ri-checkbox-multiple-line',
      }"
    />

  <!-- 👉 Others -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Others',
    }"
  />
  
  <VerticalNavLink
    :item="{
      title: 'Account Settings',
      icon: 'ri-user-settings-line',
      to: '/account-settings',
    }"
  />

  <VerticalNavLink
    :item="{
      title: 'Login',
      icon: 'ri-login-box-line',
      to: '/login',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Register',
      icon: 'ri-user-add-line',
      to: '/register',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Error',
      icon: 'ri-information-line',
      to: '/no-existence',
    }"
  />

</template>
