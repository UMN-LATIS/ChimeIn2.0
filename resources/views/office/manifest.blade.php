<?xml version="1.0" encoding="UTF-8"?>
<OfficeApp xmlns="http://schemas.microsoft.com/office/appforoffice/1.1"
           xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
           xmlns:bt="http://schemas.microsoft.com/office/officeappbasictypes/1.0"
           xsi:type="ContentApp">
  <Id>{{ config('office.content_addin_id') }}</Id>
  <Version>1.0.0.0</Version>
  <ProviderName>University of Minnesota</ProviderName>
  <DefaultLocale>en-US</DefaultLocale>
  <DisplayName DefaultValue="ChimeIn Question" />
  <Description DefaultValue="Show a live ChimeIn question on a slide, open and close it, and display results as students respond." />
  <IconUrl DefaultValue="{{ url('office/icon-64.png') }}" />
  <HighResolutionIconUrl DefaultValue="{{ url('office/icon-128.png') }}" />
  <SupportUrl DefaultValue="{{ url('/') }}" />
  <AppDomains>
    <AppDomain>{{ rtrim(url('/'), '/') }}</AppDomain>
  </AppDomains>
  <Hosts>
    <Host Name="Presentation" />
  </Hosts>
  <DefaultSettings>
    <SourceLocation DefaultValue="{{ route('office.content') }}" />
    <RequestedWidth>640</RequestedWidth>
    <RequestedHeight>480</RequestedHeight>
  </DefaultSettings>
  <Permissions>ReadWriteDocument</Permissions>
</OfficeApp>
