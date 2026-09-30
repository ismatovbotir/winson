<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js(__('admin.common.confirm_delete')))" class="inline">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-800">{{ __('admin.common.delete') }}</button>
</form>
