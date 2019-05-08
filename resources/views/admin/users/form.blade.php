<div class="form-group {{ $errors->has('name') ? 'has-error' : ''}}">
    <label for="name" class="control-label">{{ 'Name' }}</label>
    <input class="form-control" name="name" type="text" id="name" value="{{ isset($user->name) ? $user->name : ''}}" >
    {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('email') ? 'has-error' : ''}}">
    <label for="email" class="control-label">{{ 'Email' }}</label>
    <input class="form-control" name="email" type="text" id="email" value="{{ isset($user->email) ? $user->email : ''}}" >
    {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('permission_number') ? 'has-error' : ''}}">
    <label for="permission_number" class="control-label">{{ 'Permission Number' }}</label>
    <input class="form-control" name="permission_number" type="number" id="permission_number" value="{{ isset($user->permission_number) ? $user->permission_number : ''}}" >
    {!! $errors->first('permission_number', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('userType') ? 'has-error' : ''}}">
    <label for="userType" class="control-label">{{ 'Usertype' }}</label>
    <input class="form-control" name="userType" type="text" id="userType" value="{{ isset($user->userType) ? $user->userType : ''}}" >
    {!! $errors->first('userType', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('global_admin') ? 'has-error' : ''}}">
    <label for="global_admin" class="col-md-4 control-label">{{ 'Is Global Admin' }}</label>
    <div class="col-md-6">
        <div class="radio">
            <label><input name="global_admin" type="radio" value="1" {{ (isset($user) && 1 == $user->global_admin) ? 'checked' : '' }}> Yes</label>
        </div>
        <div class="radio">
            <label><input name="global_admin" type="radio" value="0" @if (isset($user)) {{ (0 == $user->global_admin) ? 'checked' : '' }} @else {{ 'checked' }} @endif> No</label>
        </div>
        {!! $errors->first('global_admin', '<p class="help-block">:message</p>') !!}
    </div>
</div>
    
    <div class="form-group">
        <input class="btn btn-primary" type="submit" value="{{ $formMode === 'edit' ? 'Update' : 'Create' }}">
    </div>
    